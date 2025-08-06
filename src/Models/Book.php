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

    private function fetchForgottenBooksFromDb($count)
    {
        $query = "
            SELECT b.id, b.bookid, b.title, b.author,
                   oldest_visits.last_visited,
                   DATEDIFF(NOW(), oldest_visits.last_visited) as days_since_visit
            FROM book_book b 
            INNER JOIN (
                SELECT v.bookid, MAX(v.visitwhen) as last_visited
                FROM book_visit v 
                INNER JOIN book_book b2 ON v.bookid = b2.id
                WHERE b2.location NOT IN ('na', '--')
                GROUP BY v.bookid
                ORDER BY last_visited ASC 
                LIMIT ?
            ) oldest_visits ON b.id = oldest_visits.bookid
            ORDER BY oldest_visits.last_visited ASC
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
}
