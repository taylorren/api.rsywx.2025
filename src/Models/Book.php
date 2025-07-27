<?php

namespace App\Models;

use App\Database\Connection;
use App\Cache\FileCache;

class Book
{
    private $db;
    private $cache;
    private $cacheTtl = 86400; // 24 hours

    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
        $this->cache = new FileCache();
    }

    public function getBookDetail($bookid, $forceRefresh = false)
    {
        $cacheKey = "book_detail_{$bookid}";
        
        // Get cached book data
        $bookData = null;
        if (!$forceRefresh) {
            $bookData = $this->cache->get($cacheKey);
        }
        
        $fromCache = ($bookData !== null);
        
        // If not cached, fetch from database
        if ($bookData === null) {
            $bookData = $this->fetchBookDataFromDb($bookid);
            
            if ($bookData === null) {
                return null; // Book not found
            }
            
            // Cache the book data
            $this->cache->set($cacheKey, $bookData, $this->cacheTtl);
        }
        
        // Always get fresh visit data
        $visitData = $this->getBookVisitData($bookData['id']);
        
        // Merge cached book data with fresh visit data
        return [
            'data' => array_merge($bookData, $visitData),
            'from_cache' => $fromCache
        ];
    }

    private function fetchBookDataFromDb($bookid)
    {
        // Get book with publisher and place names
        $query = "
            SELECT b.*, 
                   p.name as place_name, 
                   pub.name as publisher_name
            FROM book_book b
            LEFT JOIN book_place p ON b.place = p.id
            LEFT JOIN book_publisher pub ON b.publisher = pub.id
            WHERE b.bookid = ? AND b.location NOT IN ('na', '--')
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$bookid]);
        $book = $stmt->fetch();
        
        if (!$book) {
            return null;
        }
        
        // Get tags for this book
        $book['tags'] = $this->getBookTags($book['id']);
        
        // Get reviews for this book
        $book['reviews'] = $this->getBookReviews($book['id']);
        
        // Add cover page URI
        $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";
        
        return $book;
    }

    private function getBookTags($bookId)
    {
        $query = "SELECT tag FROM book_taglist WHERE bid = ? ORDER BY tag";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$bookId]);
        
        $tags = [];
        while ($row = $stmt->fetch()) {
            $tags[] = $row['tag'];
        }
        
        return $tags;
    }

    private function getBookReviews($bookId)
    {
        $query = "
            SELECT r.id, r.title, r.datein, r.uri, r.feature
            FROM book_review r
            INNER JOIN book_headline h ON r.hid = h.hid
            WHERE h.bid = ? AND h.display = 1
            ORDER BY r.datein DESC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$bookId]);
        
        return $stmt->fetchAll();
    }

    private function getBookVisitData($bookId)
    {
        $query = "
            SELECT 
                COUNT(*) as total_visits,
                MAX(visitwhen) as last_visited
            FROM book_visit 
            WHERE bookid = ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$bookId]);
        $result = $stmt->fetch();
        
        return [
            'total_visits' => (int)$result['total_visits'],
            'last_visited' => $result['last_visited']
        ];
    }

    public function clearBookCache($bookid)
    {
        $cacheKey = "book_detail_{$bookid}";
        return $this->cache->delete($cacheKey);
    }
}