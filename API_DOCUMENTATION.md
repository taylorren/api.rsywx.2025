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