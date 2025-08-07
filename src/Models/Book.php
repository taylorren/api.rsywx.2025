<?php

namespace App\Models;

use App\Database\Connection;
use App\Cache\MemoryCache;

class Book
{
    private $db;
    private $cache;
    private $cacheTtl = 86400; // 24 hours

    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
        $this->cache = new MemoryCache();
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

    public function getLatestBooks($count = 1, $forceRefresh = false)
    {
        $cacheKey = "latest_books_{$count}";

        // Get cached data
        $booksData = null;
        if (!$forceRefresh) {
            $booksData = $this->cache->get($cacheKey);
        }

        $fromCache = ($booksData !== null);

        // If not cached, fetch from database
        if ($booksData === null) {
            $booksData = $this->fetchLatestBooksFromDb($count);

            // Cache the result
            $this->cache->set($cacheKey, $booksData, $this->cacheTtl);
        }

        return [
            'data' => $booksData,
            'from_cache' => $fromCache
        ];
    }

    private function fetchLatestBooksFromDb($count)
    {
        $query = "
            SELECT b.id, b.bookid, b.title, b.author, b.purchdate, b.price,
                   p.name as place_name, 
                   pub.name as publisher_name
            FROM book_book b
            LEFT JOIN book_place p ON b.place = p.id
            LEFT JOIN book_publisher pub ON b.publisher = pub.id
            WHERE b.location NOT IN ('na', '--')
            ORDER BY b.purchdate DESC, b.id DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$count]);
        $books = $stmt->fetchAll();

        // Add cover URI to each book
        foreach ($books as &$book) {
            $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";
        }

        return $books;
    }

    public function clearBookCache($bookid)
    {
        $cacheKey = "book_detail_{$bookid}";
        return $this->cache->delete($cacheKey);
    }

    public function clearLatestBooksCache($count = null)
    {
        if ($count !== null) {
            $cacheKey = "latest_books_{$count}";
            return $this->cache->delete($cacheKey);
        }

        // For Symfony Cache, we can't easily iterate over keys, so clear all cache
        return $this->cache->clear();
    }

    public function getRandomBooks($count = 1, $forceRefresh = false)
    {
        $cacheKey = "random_books_{$count}";

        // Random books shouldn't be cached for too long, use shorter TTL
        $randomCacheTtl = 3600; // 1 hour

        // Get cached data
        $booksData = null;
        if (!$forceRefresh) {
            $booksData = $this->cache->get($cacheKey);
        }

        $fromCache = ($booksData !== null);

        // If not cached, fetch from database
        if ($booksData === null) {
            $booksData = $this->fetchRandomBooksFromDb($count);

            // Cache the result with shorter TTL
            $this->cache->set($cacheKey, $booksData, $randomCacheTtl);
        }

        return [
            'data' => $booksData,
            'from_cache' => $fromCache
        ];
    }

    private function fetchRandomBooksFromDb($count)
    {
        $query = "
            SELECT b.id, b.bookid, b.title, b.author, b.purchdate, b.price,
                   p.name as place_name, 
                   pub.name as publisher_name
            FROM book_book b
            LEFT JOIN book_place p ON b.place = p.id
            LEFT JOIN book_publisher pub ON b.publisher = pub.id
            WHERE b.location NOT IN ('na', '--')
            ORDER BY RAND()
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$count]);
        $books = $stmt->fetchAll();

        // Add cover URI and visit data to each book
        foreach ($books as &$book) {
            $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";

            // Get visit data for each random book
            $visitData = $this->getBookVisitData($book['id']);
            $book = array_merge($book, $visitData);
        }

        return $books;
    }

    public function clearRandomBooksCache($count = null)
    {
        if ($count !== null) {
            $cacheKey = "random_books_{$count}";
            return $this->cache->delete($cacheKey);
        }

        // For Symfony Cache, we can't easily iterate over keys, so clear all cache
        return $this->cache->clear();
    }

    public function getLastVisitedBooks($count = 1, $forceRefresh = false)
    {
        $cacheKey = "last_visited_books_{$count}";

        // Get cached data
        $booksData = null;
        if (!$forceRefresh) {
            $booksData = $this->cache->get($cacheKey);
        }

        $fromCache = ($booksData !== null);

        // If not cached, fetch from database
        if ($booksData === null) {
            $booksData = $this->fetchLastVisitedBooksFromDb($count);

            // Cache the result with data-driven TTL based on actual visit patterns
            // Analysis shows 80% of visits have <5min gaps, so 2min cache captures most activity
            $lastVisitedCacheTtl = 120; // 2 minutes - optimal for active reading sessions
            $this->cache->set($cacheKey, $booksData, $lastVisitedCacheTtl);
        }

        return [
            'data' => $booksData,
            'from_cache' => $fromCache
        ];
    }

    private function fetchLastVisitedBooksFromDb($count)
    {
        $query = "
            SELECT b.id, b.bookid, b.title, b.author, 
                   recent_visits.visitwhen as last_visited,
                   recent_visits.region
            FROM book_book b 
            INNER JOIN (
                SELECT v.bookid, v.visitwhen, v.region
                FROM book_visit v 
                ORDER BY v.visitwhen DESC 
                LIMIT ?
            ) recent_visits ON b.id = recent_visits.bookid
            WHERE b.location NOT IN ('na', '--')
            ORDER BY recent_visits.visitwhen DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$count]);
        $books = $stmt->fetchAll();

        // Add cover URI to each book
        foreach ($books as &$book) {
            $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";
        }

        return $books;
    }

    public function clearLastVisitedBooksCache($count = null)
    {
        if ($count !== null) {
            $cacheKey = "last_visited_books_{$count}";
            return $this->cache->delete($cacheKey);
        }

        // For Symfony Cache, we can't easily iterate over keys, so clear all cache
        return $this->cache->clear();
    }

    public function getForgottenBooks($count = 1, $forceRefresh = false)
    {
        $cacheKey = "forgotten_books_{$count}";

        // Forgotten books can have longer cache time (1 hour) since they change slowly
        $forgottenCacheTtl = 3600; // 1 hour

        // Get cached data
        $booksData = null;
        if (!$forceRefresh) {
            $booksData = $this->cache->get($cacheKey);
        }

        $fromCache = ($booksData !== null);

        // If not cached, fetch from database
        if ($booksData === null) {
            $booksData = $this->fetchForgottenBooksFromDb($count);

            // Cache the result with longer TTL
            $this->cache->set($cacheKey, $booksData, $forgottenCacheTtl);
        }

        return [
            'data' => $booksData,
            'from_cache' => $fromCache
        ];
    }
    // TODO: Based on SQL performance analysis, maybe we should add a new filed in Book_Book to capture its latest visit date as a redundancy and quick SQL
    private function fetchForgottenBooksFromDb($count)
    {
        // Simplified approach: Get the books with oldest "most recent visits"
        // This is more straightforward than the complex nested query
        $query = "
            SELECT b.id, b.bookid, b.title, b.author,
                   forgotten_visits.last_visited,
                   DATEDIFF(NOW(), forgotten_visits.last_visited) as days_since_visit
            FROM book_book b 
            INNER JOIN (
                SELECT v.bookid, MAX(v.visitwhen) as last_visited
                FROM book_visit v 
                INNER JOIN book_book b2 ON v.bookid = b2.id
                WHERE b2.location NOT IN ('na', '--')
                GROUP BY v.bookid
                ORDER BY last_visited ASC 
                LIMIT ?
            ) forgotten_visits ON b.id = forgotten_visits.bookid
            ORDER BY forgotten_visits.last_visited ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$count]);
        $books = $stmt->fetchAll();

        // Add cover URI to each book
        foreach ($books as &$book) {
            $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";
            $book['days_since_visit'] = (int)$book['days_since_visit'];
        }

        return $books;
    }

    public function clearForgottenBooksCache($count = null)
    {
        if ($count !== null) {
            $cacheKey = "forgotten_books_{$count}";
            return $this->cache->delete($cacheKey);
        }

        // For Symfony Cache, we can't easily iterate over keys, so clear all cache
        return $this->cache->clear();
    }

    public function getTodaysBooks($month = null, $date = null, $forceRefresh = false)
    {
        // Default to today's date if not provided
        $month = $month ?? (int)date('n');
        $date = $date ?? (int)date('j');
        $currentYear = date('Y');

        // Format month and date with leading zeros for consistency
        $monthDay = sprintf('%02d-%02d', $month, $date);
        $requestedDate = sprintf('%04d-%02d-%02d', $currentYear, $month, $date);
        $todayMonthDay = date('m-d');
        $isToday = ($monthDay === $todayMonthDay);

        $cacheKey = "todays_books_{$monthDay}";

        // Today's books can be cached for a full day since they only change once per day
        $todaysCacheTtl = 86400; // 24 hours

        // Get cached data
        $booksData = null;
        if (!$forceRefresh) {
            $booksData = $this->cache->get($cacheKey);
        }

        $fromCache = ($booksData !== null);

        // If not cached, fetch from database
        if ($booksData === null) {
            $booksData = $this->fetchTodaysBooksFromDb($monthDay, $currentYear);

            // Cache the result with full day TTL
            $this->cache->set($cacheKey, $booksData, $todaysCacheTtl);
        }

        return [
            'data' => $booksData,
            'from_cache' => $fromCache,
            'date_info' => [
                'requested_date' => $requestedDate,
                'month_day' => $monthDay,
                'is_today' => $isToday
            ]
        ];
    }

    private function fetchTodaysBooksFromDb($monthDay, $currentYear)
    {
        $query = "
            SELECT b.id, b.bookid, b.title, b.author, b.purchdate, b.price, b.location,
                   p.name as place_name, 
                   pub.name as publisher_name,
                   YEAR(b.purchdate) as purchase_year,
                   (? - YEAR(b.purchdate)) as years_ago
            FROM book_book b
            LEFT JOIN book_place p ON b.place = p.id
            LEFT JOIN book_publisher pub ON b.publisher = pub.id
            WHERE b.location NOT IN ('na', '--')
              AND DATE_FORMAT(b.purchdate, '%m-%d') = ?
              AND YEAR(b.purchdate) < ?
            ORDER BY b.purchdate DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$currentYear, $monthDay, $currentYear]);
        $books = $stmt->fetchAll();

        // Add cover URI to each book
        foreach ($books as &$book) {
            $book['cover_uri'] = "https://api.rsywx.com/covers/{$book['bookid']}.jpg";
            $book['years_ago'] = (int)$book['years_ago'];

            // Remove the temporary purchase_year field
            unset($book['purchase_year']);
        }

        return $books;
    }

    public function clearTodaysBooksCache()
    {
        $monthDay = date('m-d');
        $cacheKey = "todays_books_{$monthDay}";
        return $this->cache->delete($cacheKey);
    }

    public function getVisitHistory($days = 30, $forceRefresh = false)
    {
        $cacheKey = "visit_history_{$days}";

        // Visit history can be cached for a few hours since it's for trend analysis
        $visitHistoryCacheTtl = 3600; // 1 hour

        // Get cached data
        $historyData = null;
        if (!$forceRefresh) {
            $historyData = $this->cache->get($cacheKey);
        }

        $fromCache = ($historyData !== null);

        // If not cached, fetch from database
        if ($historyData === null) {
            $historyData = $this->fetchVisitHistoryFromDb($days);

            // Cache the result
            $this->cache->set($cacheKey, $historyData, $visitHistoryCacheTtl);
        }

        return [
            'data' => $historyData['daily_counts'],
            'from_cache' => $fromCache,
            'period_info' => $historyData['period_info']
        ];
    }

    private function fetchVisitHistoryFromDb($days)
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $query = "
            SELECT 
                DATE(visitwhen) as visit_date,
                COUNT(*) as visit_count
            FROM book_visit
            WHERE DATE(visitwhen) >= ?
              AND DATE(visitwhen) <= ?
            GROUP BY DATE(visitwhen)
            ORDER BY visit_date ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $results = $stmt->fetchAll();

        // Create a complete date range with zero counts for missing days
        $dailyCounts = [];
        $totalVisits = 0;
        $currentDate = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        // Create array indexed by date for quick lookup
        $visitsByDate = [];
        foreach ($results as $row) {
            $visitsByDate[$row['visit_date']] = [
                'visit_count' => (int)$row['visit_count'],
                'day_of_week' => $row['day_of_week']
            ];
            $totalVisits += (int)$row['visit_count'];
        }

        // Fill in all dates in the range
        while ($currentDate <= $endDateTime) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->format('l'); // Full day name

            if (isset($visitsByDate[$dateStr])) {
                $dailyCounts[] = [
                    'date' => $dateStr,
                    'visit_count' => $visitsByDate[$dateStr]['visit_count'],
                    'day_of_week' => $visitsByDate[$dateStr]['day_of_week']
                ];
            } else {
                $dailyCounts[] = [
                    'date' => $dateStr,
                    'visit_count' => 0,
                    'day_of_week' => $dayOfWeek
                ];
            }

            $currentDate->add(new \DateInterval('P1D'));
        }

        return [
            'daily_counts' => $dailyCounts,
            'period_info' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $days + 1, // Include both start and end dates
                'total_visits' => $totalVisits
            ]
        ];
    }

    public function clearVisitHistoryCache($days = null)
    {
        if ($days !== null) {
            $cacheKey = "visit_history_{$days}";
            return $this->cache->delete($cacheKey);
        }

        // Clear all visit history cache entries (this is a simplified approach)
        return $this->cache->clear();
    }
}
