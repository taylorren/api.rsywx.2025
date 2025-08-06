<?php

namespace App\Controllers;

use App\Models\BookStatus;
use App\Models\Book;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "RSYWX Library API",
    description: "A comprehensive API for managing your personal library collection. Access book details, collection statistics, and more."
)]
#[OA\Server(
    url: "/api/v1",
    description: "API v1"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-API-Key",
    description: "API key required for authentication"
)]
class BookController
{
    private $bookStatusModel;

    public function __construct()
    {
        $this->bookStatusModel = new BookStatus();
    }

    #[OA\Get(
        path: "/books/status",
        summary: "藏书基本信息",
        description: "返回书籍总数、总页数、总千字数，以及总访问量",
        tags: ["Collection Statistics"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Collection status",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        "total_books" => new OA\Property(property: "total_books", type: "integer", example: 1820),
                        "total_pages" => new OA\Property(property: "total_pages", type: "integer", example: 724621),
                        "total_kwords" => new OA\Property(property: "total_kwords", type: "integer", example: 483369),
                        "total_visits" => new OA\Property(property: "total_visits", type: "integer", example: 2514665)
                    ]
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function status(Request $request, Response $response)
    {
        try {
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';

            $result = $this->bookStatusModel->getCollectionStatus($forceRefresh);

            $data = [
                'success' => true,
                'data' => [
                    'total_books' => (int)$result['data']['total_books'],
                    'total_pages' => (int)$result['data']['total_pages'],
                    'total_kwords' => (int)$result['data']['total_kwords'],
                    'total_visits' => (int)$result['data']['total_visits']
                ],
                'cached' => $result['from_cache']
            ];

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/books/{bookid}",
        summary: "Get book details",
        description: "Returns detailed information for a specific book including metadata, tags, reviews, and visit statistics",
        tags: ["Book Details"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "bookid",
        in: "path",
        description: "Book ID",
        required: true,
        schema: new OA\Schema(type: "string", example: "00666")
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Book details",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        "id" => new OA\Property(property: "id", type: "integer", example: 666),
                        "bookid" => new OA\Property(property: "bookid", type: "string", example: "00666"),
                        "title" => new OA\Property(property: "title", type: "string", example: "隐形的城市"),
                        "author" => new OA\Property(property: "author", type: "string", example: "卡尔维诺"),
                        "publisher_name" => new OA\Property(property: "publisher_name", type: "string", example: "花城出版社"),
                        "place_name" => new OA\Property(property: "place_name", type: "string", example: "上海"),
                        "tags" => new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "string"), example: ["意大利", "散文", "文学", "经典"]),
                        "reviews" => new OA\Property(property: "reviews", type: "array", items: new OA\Items(type: "object")),
                        "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/00666.jpg"),
                        "total_visits" => new OA\Property(property: "total_visits", type: "integer", example: 4843),
                        "last_visited" => new OA\Property(property: "last_visited", type: "string", example: "2025-07-27 07:29:57")
                    ]
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Book not found",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: false),
                "message" => new OA\Property(property: "message", type: "string", example: "Book not found")
            ]
        )
    )]
    public function show(Request $request, Response $response, $args)
    {
        try {
            $bookid = $args['bookid'];
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';

            $bookModel = new Book();
            $result = $bookModel->getBookDetail($bookid, $forceRefresh);

            if ($result === null) {
                $errorData = [
                    'success' => false,
                    'message' => 'Book not found'
                ];

                $response->getBody()->write(json_encode($errorData));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $data = [
                'success' => true,
                'data' => $result['data'],
                'cached' => $result['from_cache']
            ];

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/books/latest/{count}",
        summary: "Get latest purchased books",
        description: "Returns the most recently purchased books, ordered by purchase date (newest first)",
        tags: ["Book Lists"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "count",
        in: "path",
        description: "Number of books to return (defaults to 1)",
        required: false,
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100, example: 5)
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Latest purchased books",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            "id" => new OA\Property(property: "id", type: "integer", example: 2083),
                            "bookid" => new OA\Property(property: "bookid", type: "string", example: "02083"),
                            "title" => new OA\Property(property: "title", type: "string", example: "维吉尔之死"),
                            "author" => new OA\Property(property: "author", type: "string", example: "布洛赫"),
                            "publisher_name" => new OA\Property(property: "publisher_name", type: "string", example: "译林出版社"),
                            "place_name" => new OA\Property(property: "place_name", type: "string", example: "苏州"),
                            "purchdate" => new OA\Property(property: "purchdate", type: "string", example: "2025-07-07"),
                            "price" => new OA\Property(property: "price", type: "number", example: 88.00),
                            "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/02083.jpg")
                        ]
                    )
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function latest(Request $request, Response $response, $args)
    {
        try {
            $count = isset($args['count']) ? (int)$args['count'] : 1;
            $count = max(1, min(100, $count)); // Ensure count is between 1 and 100

            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';

            $bookModel = new \App\Models\Book();
            $result = $bookModel->getLatestBooks($count, $forceRefresh);

            $data = [
                'success' => true,
                'data' => $result['data'],
                'cached' => $result['from_cache']
            ];

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/books/random/{count}",
        summary: "Get random books",
        description: "Returns a random selection of books from your library collection",
        tags: ["Book Lists"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "count",
        in: "path",
        description: "Number of random books to return (defaults to 1)",
        required: false,
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, example: 5)
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Random books from collection",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            "id" => new OA\Property(property: "id", type: "integer", example: 1234),
                            "bookid" => new OA\Property(property: "bookid", type: "string", example: "01234"),
                            "title" => new OA\Property(property: "title", type: "string", example: "随机书籍标题"),
                            "author" => new OA\Property(property: "author", type: "string", example: "作者姓名"),
                            "publisher_name" => new OA\Property(property: "publisher_name", type: "string", example: "出版社名称"),
                            "place_name" => new OA\Property(property: "place_name", type: "string", example: "出版地"),
                            "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/01234.jpg"),
                            "total_visits" => new OA\Property(property: "total_visits", type: "integer", example: 123),
                            "last_visited" => new OA\Property(property: "last_visited", type: "string", example: "2025-07-27 10:30:00")
                        ]
                    )
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: false)
            ]
        )
    )]
    public function random(Request $request, Response $response, $args)
    {
        try {
            $count = isset($args['count']) ? (int)$args['count'] : 1;
            $count = max(1, min(50, $count)); // Ensure count is between 1 and 50

            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';

            $bookModel = new \App\Models\Book();
            $result = $bookModel->getRandomBooks($count, $forceRefresh);

            $data = [
                'success' => true,
                'data' => $result['data'],
                'cached' => $result['from_cache']
            ];

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/books/last_visited/{count}",
        summary: "Get recently visited books",
        description: "Returns books ordered by most recent visit time (most recently visited first)",
        tags: ["Book Lists"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "count",
        in: "path",
        description: "Number of recently visited books to return (defaults to 1)",
        required: false,
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, example: 5)
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Recently visited books",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            "id" => new OA\Property(property: "id", type: "integer", example: 1234),
                            "bookid" => new OA\Property(property: "bookid", type: "string", example: "01234"),
                            "title" => new OA\Property(property: "title", type: "string", example: "最近访问的书籍"),
                            "author" => new OA\Property(property: "author", type: "string", example: "作者姓名"),
                            "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/01234.jpg"),
                            "last_visited" => new OA\Property(property: "last_visited", type: "string", example: "2025-07-27 12:30:00"),
                            "region" => new OA\Property(property: "region", type: "string", example: "home", description: "Region where the book was accessed")
                        ]
                    )
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function lastVisited(Request $request, Response $response, $args)
    {
        try {
            $count = isset($args['count']) ? (int)$args['count'] : 1;
            $count = max(1, min(50, $count)); // Ensure count is between 1 and 50
            
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';
            
            $bookModel = new \App\Models\Book();
            $result = $bookModel->getLastVisitedBooks($count, $forceRefresh);
            
            $data = [
                'success' => true,
                'data' => $result['data'],
                'cached' => $result['from_cache']
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            
            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/books/forgotten/{count}",
        summary: "Get forgotten books",
        description: "Returns books that haven't been visited for a long time, ordered by oldest visit first (most forgotten first)",
        tags: ["Book Lists"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "count",
        in: "path",
        description: "Number of forgotten books to return (defaults to 1)",
        required: false,
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, example: 5)
    )]
    #[OA\Parameter(
        name: "refresh",
        in: "query",
        description: "Force refresh cache",
        required: false,
        schema: new OA\Schema(type: "boolean", example: false)
    )]
    #[OA\Response(
        response: 200,
        description: "Forgotten books (not visited recently)",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            "id" => new OA\Property(property: "id", type: "integer", example: 1234),
                            "bookid" => new OA\Property(property: "bookid", type: "string", example: "01234"),
                            "title" => new OA\Property(property: "title", type: "string", example: "被遗忘的书籍"),
                            "author" => new OA\Property(property: "author", type: "string", example: "作者姓名"),
                            "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/01234.jpg"),
                            "last_visited" => new OA\Property(property: "last_visited", type: "string", example: "2024-01-15 10:30:00"),
                            "days_since_visit" => new OA\Property(property: "days_since_visit", type: "integer", example: 180, description: "Number of days since last visit")
                        ]
                    )
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function forgotten(Request $request, Response $response, $args)
    {
        try {
            $count = isset($args['count']) ? (int)$args['count'] : 1;
            $count = max(1, min(50, $count)); // Ensure count is between 1 and 50
            
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';
            
            $bookModel = new \App\Models\Book();
            $result = $bookModel->getForgottenBooks($count, $forceRefresh);
            
            $data = [
                'success' => true,
                'data' => $result['data'],
                'cached' => $result['from_cache']
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $errorData = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            
            $response->getBody()->write(json_encode($errorData));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
