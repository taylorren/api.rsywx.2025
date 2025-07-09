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