<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;

class ReadingController
{
    #[OA\Get(
        path: "/readings/summary",
        summary: "Get Reading Summary Statistics",
        description: "Returns reading statistics including books read count, reviews written count, and reading activity date range",
        tags: ["Reading Statistics"],
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
        description: "Reading summary statistics",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        "books_read" => new OA\Property(property: "books_read", type: "integer", example: 42),
                        "reviews_written" => new OA\Property(property: "reviews_written", type: "integer", example: 156),
                        "reading_period" => new OA\Property(
                            property: "reading_period",
                            type: "object",
                            properties: [
                                "earliest_date" => new OA\Property(property: "earliest_date", type: "string", example: "2020-01-15"),
                                "latest_date" => new OA\Property(property: "latest_date", type: "string", example: "2025-07-28"),
                                "total_days" => new OA\Property(property: "total_days", type: "integer", example: 1825)
                            ]
                        )
                    ]
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function summary(Request $request, Response $response)
    {
        try {
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';
            
            $readingModel = new \App\Models\Reading();
            $result = $readingModel->getReadingSummary($forceRefresh);
            
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
        path: "/readings/latest/{count}",
        summary: "Get Latest Readings",
        description: "Returns the most recent reading activities with book details, ordered by reading date (newest first)",
        tags: ["Reading Statistics"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Parameter(
        name: "count",
        in: "path",
        description: "Number of latest readings to return (defaults to 5)",
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
        description: "Latest reading activities",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            "hid" => new OA\Property(property: "hid", type: "integer", example: 123),
                            "bid" => new OA\Property(property: "bid", type: "integer", example: 456),
                            "bookid" => new OA\Property(property: "bookid", type: "string", example: "01234"),
                            "title" => new OA\Property(property: "title", type: "string", example: "Book Title"),
                            "author" => new OA\Property(property: "author", type: "string", example: "Author Name"),
                            "reviewtitle" => new OA\Property(property: "reviewtitle", type: "string", example: "My thoughts on this book"),
                            "create_at" => new OA\Property(property: "create_at", type: "string", example: "2025-07-14"),
                            "cover_uri" => new OA\Property(property: "cover_uri", type: "string", example: "https://api.rsywx.com/covers/01234.jpg"),
                            "reviews_count" => new OA\Property(property: "reviews_count", type: "integer", example: 3)
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
            $count = isset($args['count']) ? (int)$args['count'] : 5;
            $count = max(1, min(50, $count)); // Ensure count is between 1 and 50
            
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';
            
            $readingModel = new \App\Models\Reading();
            $result = $readingModel->getLatestReadings($count, $forceRefresh);
            
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