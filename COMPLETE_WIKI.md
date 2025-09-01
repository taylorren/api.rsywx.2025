# RSYWX API 2025 - Complete Repository Wiki

**Generated on:** 2025-08-23 12:24:25

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Project Structure](#project-structure)
3. [API Documentation](#api-documentation)
4. [Database Information](#database-information)
5. [Dependencies](#dependencies)
6. [Configuration](#configuration)
7. [Testing](#testing)

---

# Project Overview

# RSYWX API 2025

A PHP + Slim Framework API for personal library management system.

## Features

- **Book Collection Status** - Get total books, pages, keywords, and visits
- **Book Details** - Retrieve detailed book information by book ID
- **Smart Caching** - 24-hour TTL for static data, real-time for dynamic data
- **API Key Authentication** - Secure access control
- **Apache Integration** - Ready for production deployment

## API Documentation

Visit the root URL (`/`) to access the interactive API documentation with detailed endpoint specifications, request/response examples, and data models.

## API Endpoints

### Collection Status
```
GET /api/v1/books/status
```
Returns total books, pages, keywords, and visits count.

### Book Details
```
GET /api/v1/books/{bookid}
```
Returns detailed book information including:
- Basic book data (title, author, ISBN, etc.)
- Publisher and place names
- Tags and reviews
- Cover image URI
- Visit statistics (real-time)

### Authentication
All endpoints (except `/health` and `/`) require API key authentication via:
- Header: `X-API-Key: your-api-key`
- Query parameter: `?api_key=your-api-key`

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/taylorren/api.rsywx.2025.git
   cd api.rsywx.2025
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and API key
   ```

4. **Set up Apache virtual host**
   ```bash
   sudo cp apache-vhost.conf /etc/apache2/sites-available/api.conf
   sudo a2ensite api.conf
   sudo a2enmod rewrite headers expires deflate
   sudo systemctl restart apache2
   ```

5. **Set cache permissions**
   ```bash
   mkdir -p cache
   sudo chown -R www-data:www-data cache/
   ```

## Configuration

### Environment Variables
- `DB_HOST` - Database host
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASSWORD` - Database password
- `API_KEY` - Your secure API key

### Database Schema
The API works with the existing RSYWX database schema including:
- `book_book` - Main books table
- `book_publisher` - Publishers
- `book_place` - Storage locations
- `book_taglist` - Book tags
- `book_review` - Book reviews
- `book_visit` - Visit tracking

## Caching

The API implements intelligent caching:
- **Static data** (book details, tags, reviews) cached for 24 hours
- **Dynamic data** (visit counts, last visited) always fresh
- **Manual refresh** available with `?refresh=true` parameter
- **File-based cache** stored in `/cache` directory

## Security

- API key authentication required
- CORS headers configured
- Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- Input validation and sanitization
- Error handling without information disclosure

## Development

### Health Check
```bash
curl http://your-domain/health
```

### Testing Endpoints
```bash
# Collection status
curl -H "X-API-Key: your-key" http://your-domain/api/v1/books/status

# Book details
curl -H "X-API-Key: your-key" http://your-domain/api/v1/books/00666
```

## License

MIT License

## Author

Taylor Ren - Personal Library Management System

---

# Project Structure

```
├── public/
│   ├── .htaccess
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
├── .env.example
├── .gitignore
├── API_DOCUMENTATION.md
├── README.md
├── apache-vhost.conf
├── composer.json
├── composer.lock
├── export-wiki-consolidated.php
├── export-wiki.php
├── generate-docs.php
├── phpunit.xml
├── schema.rsywx.sql
```


---

# API Documentation

# 图书管理API文档

## 概述

这是一个基于Slim 4框架构建的图书管理API后台系统，提供完整的图书CRUD操作、缓存机制和访问统计功能。

## 基础信息

- **基础URL**: `http://your-domain.com/api/v1`
- **认证方式**: API Key (通过Header `X-API-Key` 或查询参数 `api_key`)
- **响应格式**: JSON
- **字符编码**: UTF-8

## 认证

所有API请求（除了健康检查）都需要提供有效的API Key：

### Header方式
```
X-API-Key: your-api-key-here
```

### 查询参数方式
```
GET /api/v1/books?api_key=your-api-key-here
```

## 通用响应格式

### 成功响应
```json
{
    "success": true,
    "data": {},
    "cached": false
}
```

### 错误响应
```json
{
    "success": false,
    "message": "错误描述"
}
```

## API端点

### 1. 健康检查

检查API服务状态（无需认证）。

**请求**
```
GET /health
```

**响应**
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2025-01-20 10:30:00"
}
```

### 2. 获取图书集合状态

获取图书库的统计信息，包括总数、页数、字数和访问量。

**请求**
```
GET /api/v1/books/status
```

**查询参数**
- `refresh` (可选): `true` 强制刷新缓存

**响应**
```json
{
    "success": true,
    "data": {
        "total_books": 2077,
        "total_pages": 425678,
        "total_kwords": 89234,
        "total_visits": 2351070
    },
    "cached": true
}
```

### 3. 获取图书列表

获取图书列表，支持分页、过滤和排序。

**请求**
```
GET /api/v1/books
```

**查询参数**
- `page` (可选): 页码，默认为1
- `limit` (可选): 每页数量，默认为20
- `title` (可选): 按标题过滤
- `author` (可选): 按作者过滤
- `category` (可选): 按分类过滤
- `instock` (可选): 按库存状态过滤 (`true`/`false`)
- `order_by` (可选): 排序字段 (如: `title`, `author`, `pubdate`)
- `order_dir` (可选): 排序方向 (`ASC`/`DESC`)
- `refresh` (可选): `true` 强制刷新缓存

**响应**
```json
{
    "success": true,
    "data": {
        "books": [
            {
                "id": 1,
                "bookid": "B001",
                "title": "示例图书",
                "author": "作者姓名",
                "region": "中国",
                "copyrighter": "版权方",
                "translated": false,
                "purchase_date": "2024-01-15",
                "price": "¥29.80",
                "publication_date": "2023-12-01",
                "print_date": "2023-12-15",
                "version": "1",
                "decoration": "平装",
                "word_count": "25.6万字",
                "pages": 320,
                "isbn": "978-7-111-12345-6",
                "category": "技术",
                "online": "Y",
                "introduction": "图书简介...",
                "in_stock": true,
                "location": "A01",
                "book_age": 1,
                "reviews": [],
                "total_visits": 156,
                "last_visit_time": "2024-01-20 09:30:00",
                "tags": ["编程", "技术"]
            }
        ],
        "pagination": {
            "page": 1,
            "limit": 20,
            "total": 20
        }
    },
    "cached": true
}
```

### 4. 根据ID获取单个图书

根据数据库ID获取图书详细信息。

**请求**
```
GET /api/v1/books/{id}
```

**路径参数**
- `id`: 图书的数据库ID

**查询参数**
- `refresh` (可选): `true` 强制刷新缓存

**响应**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "bookid": "B001",
        "title": "示例图书",
        // ... 完整的图书信息
    },
    "cached": true
}
```

### 5. 根据BookID获取单个图书

根据图书编号获取图书详细信息。

**请求**
```
GET /api/v1/books/bookid/{bookid}
```

**路径参数**
- `bookid`: 图书编号

**查询参数**
- `refresh` (可选): `true` 强制刷新缓存

**响应**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "bookid": "B001",
        "title": "示例图书",
        // ... 完整的图书信息
    },
    "cached": true
}
```

### 6. 创建新图书

创建一本新图书。

**请求**
```
POST /api/v1/books
Content-Type: application/json

{
    "bookid": "B002",
    "title": "新图书标题",
    "author": "作者姓名",
    "region": "中国",
    "translated": false,
    "purchdate": "2024-01-20",
    "price": 35.50,
    "pubdate": "2024-01-01",
    "printdate": "2024-01-10",
    "ver": "1",
    "deco": "平装",
    "kword": 280000,
    "page": 350,
    "isbn": "978-7-111-54321-0",
    "category": "文学",
    "intro": "这是一本关于...",
    "instock": true,
    "location": "B02"
}
```

**响应**
```json
{
    "success": true,
    "message": "图书创建成功",
    "data": {
        "id": 2078,
        "bookid": "B002",
        // ... 完整的图书信息
    }
}
```

### 7. 更新图书

更新现有图书信息。

**请求**
```
PUT /api/v1/books/{id}
Content-Type: application/json

{
    "title": "更新后的标题",
    "price": 39.80,
    "instock": false
    // ... 其他需要更新的字段
}
```

**响应**
```json
{
    "success": true,
    "message": "图书更新成功",
    "data": {
        "id": 1,
        "title": "更新后的标题",
        // ... 完整的图书信息
    }
}
```

### 8. 删除图书

删除指定图书。

**请求**
```
DELETE /api/v1/books/{id}
```

**响应**
```json
{
    "success": true,
    "message": "图书删除成功"
}
```

## 图书实体字段说明

| 字段名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 否 | 数据库自增ID |
| place | integer | 否 | 购买地点ID |
| publisher | integer | 否 | 出版社ID |
| bookid | string | 是 | 图书编号（唯一） |
| title | string | 是 | 图书标题 |
| author | string | 是 | 作者 |
| region | string | 是 | 地区 |
| copyrighter | string | 否 | 版权方 |
| translated | boolean | 否 | 是否翻译作品 |
| purchdate | date | 否 | 购买日期 |
| price | float | 是 | 价格 |
| pubdate | date | 否 | 出版日期 |
| printdate | date | 否 | 印刷日期 |
| ver | string | 是 | 版本 |
| deco | string | 是 | 装帧方式 |
| kword | integer | 是 | 字数（千字） |
| page | integer | 是 | 页数 |
| isbn | string | 是 | ISBN号码 |
| category | string | 否 | 分类 |
| ol | string | 否 | 在线状态 |
| intro | string | 是 | 简介 |
| instock | boolean | 是 | 是否有库存 |
| location | string | 否 | 存放位置 |

## 缓存机制

系统实现了智能缓存机制来优化性能：

### 缓存策略
- **基本图书信息**: 缓存24小时，包括图书的基本属性
- **实时数据**: 不缓存，包括评论、访问统计、最后访问时间
- **列表查询**: 缓存24小时，按查询条件分别缓存

### 缓存控制
- 使用 `refresh=true` 参数强制刷新缓存
- 创建、更新、删除操作会自动清除相关缓存
- 响应中的 `cached` 字段表示数据是否来自缓存

## 访问统计

系统会自动记录图书访问情况：
- 每次获取单个图书详情时会记录访问
- 记录访问时间、IP地址等信息
- 提供总访问量和最后访问时间

## 错误代码

| HTTP状态码 | 说明 |
|------------|------|
| 200 | 请求成功 |
| 201 | 创建成功 |
| 400 | 请求参数错误 |
| 401 | 认证失败 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

## 使用示例

### 获取图书列表（带过滤）
```bash
curl -H "X-API-Key: your-api-key" \
     "http://your-domain.com/api/v1/books?author=张三&category=技术&page=1&limit=10"
```

### 创建新图书
```bash
curl -X POST \
     -H "X-API-Key: your-api-key" \
     -H "Content-Type: application/json" \
     -d '{
       "bookid": "B003",
       "title": "PHP高级编程",
       "author": "李四",
       "region": "中国",
       "price": 89.00,
       "page": 500,
       "kword": 400,
       "ver": "1",
       "deco": "精装",
       "isbn": "978-7-111-99999-9",
       "intro": "深入学习PHP高级特性",
       "instock": true
     }' \
     "http://your-domain.com/api/v1/books"
```

### 更新图书信息
```bash
curl -X PUT \
     -H "X-API-Key: your-api-key" \
     -H "Content-Type: application/json" \
     -d '{"price": 79.00, "instock": false}' \
     "http://your-domain.com/api/v1/books/1"
```

## 注意事项

1. **API Key安全**: 请妥善保管API Key，不要在客户端代码中暴露
2. **数据验证**: 创建和更新图书时会进行数据验证，请确保提供有效数据
3. **缓存刷新**: 在需要最新数据时使用 `refresh=true` 参数
4. **并发访问**: 系统支持并发访问，但建议合理控制请求频率
5. **数据备份**: 建议定期备份数据库数据

## 技术栈

- **框架**: Slim 4
- **数据库**: MySQL 8.0
- **缓存**: 文件缓存系统
- **认证**: API Key
- **日志**: Monolog（可选）

## 版本信息

- **当前版本**: v1
- **最后更新**: 2025-01-20
- **兼容性**: PHP 8.0+

---

# Database Schema Information

## Database Tables

- `book_book`
- `book_headline`
- `book_place`
- `book_publisher`
- `book_review`
- `book_taglist`
- `book_visit`
- `lakers`
- `qotd`
- `wotd`
- `wpme`

## Schema File Size
- Size: 10,224 bytes
- Lines: 243


---

# Project Dependencies

## Production Dependencies

- `php`: >=7.4
- `slim/slim`: ^4.0
- `slim/psr7`: ^1.0
- `php-di/php-di`: ^6.0
- `monolog/monolog`: ^2.0
- `vlucas/phpdotenv`: ^5.0
- `zircote/swagger-php`: ^4.0

## Development Dependencies

- `phpdocumentor/phpdocumentor`: ^3.0
- `phpunit/phpunit`: ^9.0


---

# Configuration Files

## Environment Configuration

```bash
# Database Configuration
DB_HOST=localhost
DB_NAME=rsywx
DB_USER=root
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4

# API Configuration
API_KEY=your-secret-api-key-here
API_VERSION=v1

# Environment
APP_ENV=development
LOG_LEVEL=debug

# Rate Limiting
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=3600
```

## Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName api
    ServerAlias api.yourdomain.com
    DocumentRoot /home/tr/www/api.rsywx.2025/public
    
    <Directory /home/tr/www/api.rsywx.2025/public>
        AllowOverride All
        Require all granted
        
        # Enable URL rewriting for Slim
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ index.php [QSA,L]
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # PHP settings
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value memory_limit 256M
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/api_error.log
    CustomLog ${APACHE_LOG_DIR}/api_access.log combined
</VirtualHost>
```

---

# Testing Information

## PHPUnit Configuration

Configuration file: `phpunit.xml`

## Test Summary

# API Test Summary

This document summarizes the comprehensive test coverage for the RSYWX Library API.

## Test Statistics
- **Total Tests**: 44
- **Total Assertions**: 559
- **Test Files**: 3
- **Coverage**: All major endpoints and edge cases

## Test Files

### 1. BaseTestCase.php
- Provides common test infrastructure
- Sets up test environment with proper middleware
- Configures all API routes for testing
- Handles database connection gracefully

### 2. ApiEndpointsTest.php (25 tests, 321 assertions)
Tests all the main API endpoints we've developed:

#### Core Endpoints
- ✅ Health endpoint (`/health`)
- ✅ API key authentication (header and query parameter)
- ✅ CORS headers validation

#### Book Status Endpoint (`/books/status`)
- ✅ Returns collection statistics
- ✅ Proper data structure validation
- ✅ Cache functionality

#### Book Detail Endpoint (`/books/{bookid}`)
- ✅ Valid book retrieval
- ✅ Invalid book handling (404)
- ✅ Proper data structure validation

#### Latest Books Endpoint (`/books/latest[/{count}]`)
- ✅ Default count (1 book)
- ✅ Custom count (3 books)
- ✅ Proper data structure validation

#### Random Books Endpoint (`/books/random[/{count}]`)
- ✅ Default count (1 book)
- ✅ Custom count (5 books)
- ✅ Maximum count limit (50 books)
- ✅ Count validation (caps at 50 even if 100 requested)
- ✅ Cache refresh functionality
- ✅ API key requirement
- ✅ Proper data structure validation

#### Last Visited Books Endpoint (`/books/last_visited[/{count}]`)
- ✅ Default count (1 book)
- ✅ Custom count (5 books)
- ✅ Maximum count limit (50 books)
- ✅ Chronological ordering validation (most recent first)
- ✅ Cache refresh functionality
- ✅ API key requirement
- ✅ Region information validation

#### Forgotten Books Endpoint (`/books/forgotten[/{count}]`)
- ✅ Default count (1 book)
- ✅ Custom count (5 books)
- ✅ Maximum count limit (50 books)
- ✅ Chronological ordering validation (oldest visit first)
- ✅ Days since visit calculation
- ✅ Cache refresh functionality
- ✅ API key requirement

### 3. TodaysBooksEndpointTest.php (15 tests, 220 assertions)
Comprehensive testing of the "today's books" endpoint:

#### Basic Functionality
- ✅ Default endpoint (`/books/today`) - returns today's historical books
- ✅ Parameterized endpoint (`/books/today/{month}/{date}`)
- ✅ Proper date_info structure validation
- ✅ Book data structure validation

#### Date Validation
- ✅ Valid dates (Christmas: 12/25, New Year's: 1/1)
- ✅ Leap year support (February 29th)
- ✅ Invalid date rejection (February 30th, April 31st)
- ✅ Invalid month/day ranges (month 0, 13; day 0, 32)
- ✅ Boundary date testing (all month-end dates)

#### Data Integrity
- ✅ Books match requested date (month/day)
- ✅ Books are from previous years only
- ✅ Years_ago calculation accuracy
- ✅ Leap year validation for Feb 29 books

#### API Features
- ✅ Cache functionality and refresh
- ✅ API key requirement (header and query)
- ✅ Proper error responses (400 for invalid dates)
- ✅ CORS headers

#### Edge Cases
- ✅ Leap year date handling (Feb 29)
- ✅ Month/day boundary conditions
- ✅ Zero values rejection
- ✅ Out-of-range values rejection

## Test Coverage Summary

### Endpoints Tested
1. ✅ `/health` - Health check
2. ✅ `/api/v1/books/status` - Collection statistics
3. ✅ `/api/v1/books/latest[/{count}]` - Latest purchased books
4. ✅ `/api/v1/books/random[/{count}]` - Random books
5. ✅ `/api/v1/books/last_visited[/{count}]` - Recently visited books
6. ✅ `/api/v1/books/forgotten[/{count}]` - Forgotten books
7. ✅ `/api/v1/books/today` - Today's historical books
8. ✅ `/api/v1/books/today/{month}/{date}` - Specific date historical books
9. ✅ `/api/v1/books/{bookid}` - Individual book details

### Features Tested
- ✅ Authentication (API key validation)
- ✅ CORS headers
- ✅ Caching functionality
- ✅ Cache refresh mechanism
- ✅ Input validation
- ✅ Error handling (400, 401, 404, 500)
- ✅ Data structure validation
- ✅ Date validation and leap year support
- ✅ Pagination limits
- ✅ Chronological ordering
- ✅ Database graceful failure handling

### Data Validation
- ✅ Response structure consistency
- ✅ Data type validation (integers, strings, booleans)
- ✅ Required field presence
- ✅ Date format validation
- ✅ URL structure validation
- ✅ Cache status reporting

## Running Tests

```bash
# Run all tests
php vendor/bin/phpunit tests/ --verbose

# Run specific test file
php vendor/bin/phpunit tests/Integration/TodaysBooksEndpointTest.php --verbose
php vendor/bin/phpunit tests/Integration/ApiEndpointsTest.php --verbose

# Run with coverage (if xdebug enabled)
php vendor/bin/phpunit tests/ --coverage-html coverage/
```

## Test Environment
- **PHP Version**: 8.3.6
- **PHPUnit Version**: 9.6.23
- **Database**: MySQL (with graceful failure handling)
- **Environment**: Testing mode with test API key
- **Memory Usage**: ~8MB per test run
- **Execution Time**: ~10 seconds for full suite

## Notes
- Tests include database connection error handling
- All tests pass even without database connectivity
- Comprehensive edge case coverage
- Performance validation included
- Security testing (API key requirements)
- CORS compliance verification
## Test Files Count

- Total test files: 4


