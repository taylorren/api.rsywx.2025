<?php

namespace App\Models;

use App\Database\Connection;

class BookQueryBuilder
{
    private $db;
    private $baseQuery;
    private $joins = [];
    private $fields = [];
    private $conditions = [];
    private $orderBy = [];
    private $limit = null;
    
    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
        $this->baseQuery = "FROM book_book b";
        $this->addField('b.id', 'id');
        $this->addField('b.bookid', 'bookid');
        $this->addField('b.title', 'title');
        $this->addField('b.author', 'author');
        $this->addField('b.translated', 'translated');
        $this->addField('b.copyrighter', 'copyrighter');
        $this->addField('b.region', 'region');
        $this->addField('b.location', 'location');
    }
    
    public function includeFields(array $fieldGroups)
    {
        foreach ($fieldGroups as $group) {
            switch ($group) {
                case 'purchase':
                    $this->includePurchaseFields();
                    break;
                case 'visits':
                    $this->includeVisitFields();
                    break;
                case 'visit_stats':
                    $this->includeVisitStats();
                    break;
                case 'computed':
                    $this->includeComputedFields();
                    break;
                case 'publication':
                    $this->includePublicationFields();
                    break;
                case 'rich':
                    $this->includeRichFields();
                    break;
            }
        }
        return $this;
    }
    
    private function includePurchaseFields()
    {
        $this->addField('b.purchdate', 'purchdate');
        $this->addField('b.price', 'price');
        $this->addField('p.name', 'place_name');
        $this->addField('pub.name', 'publisher_name');
        
        $this->addJoin('LEFT JOIN book_place p ON b.place = p.id');
        $this->addJoin('LEFT JOIN book_publisher pub ON b.publisher = pub.id');
        
        return $this;
    }
    
    private function includeVisitFields()
    {
        // For last visited books - get the most recent visit info
        $this->addField('recent_visits.visitwhen', 'last_visited');
        $this->addField('recent_visits.region', 'region');
        
        return $this;
    }
    
    private function includeVisitStats()
    {
        // For books that need total visit counts
        $this->addJoin('LEFT JOIN (
            SELECT bookid, COUNT(*) as total_visits, MAX(visitwhen) as last_visited
            FROM book_visit 
            GROUP BY bookid
        ) visit_stats ON b.id = visit_stats.bookid');
        
        $this->addField('COALESCE(visit_stats.total_visits, 0)', 'total_visits');
        $this->addField('visit_stats.last_visited', 'last_visited');
        
        return $this;
    }
    
    private function includeComputedFields()
    {
        // Ensure visit_stats is included for computed fields
        $this->includeVisitStats();
        
        // Add computed fields like days_since_visit, years_ago
        $this->addField('DATEDIFF(NOW(), visit_stats.last_visited)', 'days_since_visit');
        
        return $this;
    }
    
    private function includePublicationFields()
    {
        $this->addField('p.name', 'place_name');
        $this->addField('pub.name', 'publisher_name');
        
        $this->addJoin('LEFT JOIN book_place p ON b.place = p.id');
        $this->addJoin('LEFT JOIN book_publisher pub ON b.publisher = pub.id');
        
        return $this;
    }
    
    private function includeRichFields()
    {
        // For detailed book view - tags and reviews would be loaded separately
        // to avoid complex JOINs that could cause performance issues
        return $this;
    }
    
    public function latest($count = 1)
    {
        $this->orderBy('b.purchdate DESC, b.id DESC');
        $this->limit($count);
        return $this;
    }
    
    public function random($count = 1)
    {
        $this->orderBy('RAND()');
        $this->limit($count);
        return $this;
    }
    
    public function lastVisited($count = 1)
    {
        // Use subquery for most recent visits
        $this->addJoin('INNER JOIN (
            SELECT v.bookid, v.visitwhen, v.country
            FROM book_visit v 
            ORDER BY v.visitwhen DESC 
            LIMIT ' . (int)$count . '
        ) recent_visits ON b.id = recent_visits.bookid');
        
        // Include visit fields - country is more useful than visit region
        $this->addField('recent_visits.visitwhen', 'last_visited');
        $this->addField('recent_visits.country', 'visit_country');
        
        $this->orderBy('recent_visits.visitwhen DESC');
        return $this;
    }
    
    public function forgotten($count = 1)
    {
        // Use subquery for oldest last visits
        $this->addJoin('INNER JOIN (
            SELECT v.bookid, MAX(v.visitwhen) as last_visited
            FROM book_visit v 
            INNER JOIN book_book b2 ON v.bookid = b2.id
            WHERE b2.location NOT IN (\'na\', \'--\')
            GROUP BY v.bookid
            ORDER BY last_visited ASC 
            LIMIT ' . (int)$count . '
        ) forgotten_visits ON b.id = forgotten_visits.bookid');
        
        $this->addField('forgotten_visits.last_visited', 'last_visited');
        $this->orderBy('forgotten_visits.last_visited ASC');
        return $this;
    }
    
    public function todaysBooks($month, $date)
    {
        $currentYear = date('Y');
        $monthDay = sprintf('%02d-%02d', $month, $date);
        
        $this->addCondition("DATE_FORMAT(b.purchdate, '%m-%d') = ?", $monthDay);
        $this->addCondition("YEAR(b.purchdate) < ?", $currentYear);
        $this->addField("({$currentYear} - YEAR(b.purchdate))", 'years_ago');
        $this->orderBy('b.purchdate DESC');
        
        return $this;
    }
    
    public function byId($bookId)
    {
        $this->addCondition('b.id = ?', $bookId);
        $this->limit(1);
        return $this;
    }
    
    public function byBookId($bookId)
    {
        $this->addCondition('b.bookid = ?', $bookId);
        $this->limit(1);
        return $this;
    }
    
    private function addField($field, $alias = null)
    {
        if ($alias) {
            $this->fields[] = "{$field} as {$alias}";
        } else {
            $this->fields[] = $field;
        }
    }
    
    private function addJoin($join)
    {
        if (!in_array($join, $this->joins)) {
            $this->joins[] = $join;
        }
    }
    
    private function addCondition($condition, $value = null)
    {
        $this->conditions[] = ['condition' => $condition, 'value' => $value];
    }
    
    private function orderBy($orderBy)
    {
        $this->orderBy[] = $orderBy;
    }
    
    private function limit($limit)
    {
        $this->limit = (int)$limit;
    }
    
    public function execute()
    {
        // Always exclude invalid locations
        $this->addCondition("b.location NOT IN ('na', '--')");
        
        // Build the complete query
        $query = "SELECT " . implode(', ', $this->fields) . " ";
        $query .= $this->baseQuery . " ";
        $query .= implode(' ', $this->joins) . " ";
        
        if (!empty($this->conditions)) {
            $whereConditions = array_map(function($c) { return $c['condition']; }, $this->conditions);
            $query .= "WHERE " . implode(' AND ', $whereConditions) . " ";
        }
        
        if (!empty($this->orderBy)) {
            $query .= "ORDER BY " . implode(', ', $this->orderBy) . " ";
        }
        
        if ($this->limit) {
            $query .= "LIMIT {$this->limit}";
        }
        
        // Prepare and execute
        $stmt = $this->db->prepare($query);
        
        // Bind parameters
        $paramIndex = 1;
        foreach ($this->conditions as $condition) {
            if ($condition['value'] !== null) {
                $stmt->bindValue($paramIndex++, $condition['value']);
            }
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Convert to BookResponse objects
        $books = [];
        foreach ($results as $row) {
            $books[] = BookResponse::fromDatabaseRow($row);
        }
        
        return $books;
    }
    
    public function executeOne()
    {
        $this->limit(1);
        $results = $this->execute();
        return !empty($results) ? $results[0] : null;
    }
}