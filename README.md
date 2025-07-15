# RSYWX API

A Symfony-based API for the RSYWX website.

## Features

- Book details API with optimized performance
- Caching mechanism for frequently accessed data
- Comprehensive test coverage

## API Endpoints

### Books

- `GET /books/detail/{bookid}`: Get detailed information about a book
  - Returns book information, visit statistics, tags, and reviews
  - Implements caching for improved performance
  - Returns 404 if book not found

- `GET /books/random`: Get random book(s)
  - Returns an array of random books (even when requesting a single book)
  - Optional `count` parameter to specify number of books (default: 1, max: 10)
  - Includes visit statistics and detailed book information
  - See [Random Book API Documentation](docs/RandomBookAPI.md) for details

- `GET /books/recent_visit`: Get recently visited book(s)
  - Returns an array of recently visited books (even when requesting a single book)
  - Optional `count` parameter to specify number of books (default: 1, max: 10)
  - Books are sorted by visit time in descending order (most recent first)
  - Includes visit statistics and detailed book information
  - See [Recent Visited Books API Documentation](docs/RecentVisitedBooksAPI.md) for details

## Development

### Requirements

- PHP 8.3+
- Symfony 7.0+
- Doctrine ORM

### Testing

Run tests with PHPUnit:

```bash
php bin/phpunit
```

### Performance Optimizations

- Native SQL queries for complex data retrieval
- Response caching (1 hour TTL)
- Optimized database interactions