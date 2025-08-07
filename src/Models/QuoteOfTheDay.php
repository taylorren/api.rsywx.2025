<?php

namespace App\Models;

use App\Database\Connection;
use App\Cache\MemoryCache;

class QuoteOfTheDay
{
    private $db;
    private $cache;

    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
        $this->cache = new MemoryCache();
    }

    public function getQuoteOfTheDay($forceRefresh = false)
    {
        $today = date('Y-m-d');
        $cacheKey = "quote_of_the_day_{$today}";

        // Cache for 24 hours since quote changes daily
        $quoteCacheTtl = 86400; // 24 hours

        // Get cached data
        $quoteData = null;
        if (!$forceRefresh) {
            $quoteData = $this->cache->get($cacheKey);
        }

        $fromCache = ($quoteData !== null);

        // If not cached, fetch from database
        if ($quoteData === null) {
            $quoteData = $this->fetchQuoteOfTheDayFromDb();

            // Cache the result
            $this->cache->set($cacheKey, $quoteData, $quoteCacheTtl);
        }

        return [
            'data' => $quoteData,
            'from_cache' => $fromCache
        ];
    }

    private function fetchQuoteOfTheDayFromDb()
    {
        // Use day of year to get a consistent quote for each day
        $dayOfYear = (int)date('z') + 1; // +1 because date('z') is 0-based
        $today = date('Y-m-d');

        // Get a deterministic quote based on day of year
        // This ensures the same quote appears for the entire day
        $query = "SELECT id, quote, author FROM qotd ORDER BY id LIMIT 1 OFFSET ?";

        $stmt = $this->db->prepare($query);
        
        // Use modulo to cycle through available quotes
        $totalQuotesQuery = "SELECT COUNT(*) as total FROM qotd";
        $totalStmt = $this->db->prepare($totalQuotesQuery);
        $totalStmt->execute();
        $totalQuotes = $totalStmt->fetch()['total'];
        
        if ($totalQuotes == 0) {
            // Fallback if no quotes in database
            return [
                'quote' => 'The journey of a thousand miles begins with one step.',
                'author' => 'Lao Tzu',
                'date' => $today,
                'day_of_year' => $dayOfYear
            ];
        }

        $offset = ($dayOfYear - 1) % $totalQuotes;
        $stmt->execute([$offset]);
        $quote = $stmt->fetch();

        return [
            'quote' => $quote['quote'],
            'author' => $quote['author'],
            'date' => $today,
            'day_of_year' => $dayOfYear
        ];
    }

    public function clearQuoteOfTheDayCache()
    {
        $today = date('Y-m-d');
        $cacheKey = "quote_of_the_day_{$today}";
        return $this->cache->delete($cacheKey);
    }
}