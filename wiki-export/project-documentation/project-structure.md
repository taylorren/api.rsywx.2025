# Project Structure

This document outlines the complete project structure and organization.

## Directory Structure

```
├── public/
│   ├── api-docs.css
│   ├── api-docs.html
│   ├── api-docs.json
│   ├── api-docs.yaml
│   ├── index.php
├── src/
│   ├── Cache/
│   │   ├── MemoryCache.php
│   ├── Controllers/
│   │   ├── BookController.php
│   │   ├── MiscController.php
│   │   ├── ReadingController.php
│   │   ├── SystemController.php
│   ├── Database/
│   │   ├── Connection.php
│   ├── Models/
│   │   ├── Book.php
│   │   ├── BookQueryBuilder.php
│   │   ├── BookResponse.php
│   │   ├── BookStatus.php
│   │   ├── Misc.php
│   │   ├── QuoteOfTheDay.php
│   │   ├── Reading.php
│   │   ├── Weather.php
│   │   ├── WordOfTheDay.php
├── tests/
│   ├── Integration/
│   │   ├── ApiEndpointsTest.php
│   │   ├── BookListEndpointTest.php
│   │   ├── TodaysBooksEndpointTest.php
│   ├── Unit/
│   │   ├── BookStatusTest.php
│   ├── BaseTestCase.php
│   ├── TEST_SUMMARY.md
├── wiki-export/
│   ├── api-documentation/
│   │   ├── api-documentation.md
│   │   ├── docs-styles.css
│   │   ├── interactive-docs.html
│   │   ├── openapi-spec.json
│   │   ├── openapi-spec.yaml
│   ├── project-documentation/
│   │   ├── dependencies.json
│   │   ├── readme.md
│   │   ├── testing-config.xml
├── API_DOCUMENTATION.md
├── README.md
├── apache-vhost.conf
├── composer.json
├── composer.lock
├── export-wiki.php
├── generate-docs.php
├── phpunit.xml
├── schema.rsywx.sql
```

## Key Components

- **public/**: Web server document root with API entry point
- **src/**: Application source code organized by MVC pattern
- **tests/**: Unit and integration tests
- **vendor/**: Composer dependencies
- **cache/**: Application cache storage

