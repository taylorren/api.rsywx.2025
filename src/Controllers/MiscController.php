<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;

class MiscController
{
    #[OA\Get(
        path: "/misc/wotd",
        summary: "Get Word of the Day",
        description: "Returns a random word of the day with its meaning, example sentence, and word type",
        tags: ["Miscellaneous"],
        security: [["ApiKeyAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Word of the Day",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        "id" => new OA\Property(property: "id", type: "integer", example: 42),
                        "word" => new OA\Property(property: "word", type: "string", example: "serendipity"),
                        "meaning" => new OA\Property(property: "meaning", type: "string", example: "The occurrence of events by chance in a happy way"),
                        "sentence" => new OA\Property(property: "sentence", type: "string", example: "It was pure serendipity that led to their meeting."),
                        "type" => new OA\Property(property: "type", type: "string", example: "noun")
                    ]
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: false)
            ]
        )
    )]
    public function wordOfTheDay(Request $request, Response $response)
    {
        try {
            $wordModel = new \App\Models\WordOfTheDay();
            $result = $wordModel->getWordOfTheDay();
            
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
        path: "/misc/qotd",
        summary: "Get Quote of the Day",
        description: "Returns a daily quote, changes once per day based on the current date",
        tags: ["Miscellaneous"],
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
        description: "Quote of the Day",
        content: new OA\JsonContent(
            properties: [
                "success" => new OA\Property(property: "success", type: "boolean", example: true),
                "data" => new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        "quote" => new OA\Property(property: "quote", type: "string", example: "The only way to do great work is to love what you do."),
                        "author" => new OA\Property(property: "author", type: "string", example: "Steve Jobs"),
                        "date" => new OA\Property(property: "date", type: "string", example: "2025-08-07"),
                        "day_of_year" => new OA\Property(property: "day_of_year", type: "integer", example: 219)
                    ]
                ),
                "cached" => new OA\Property(property: "cached", type: "boolean", example: true)
            ]
        )
    )]
    public function qotd(Request $request, Response $response)
    {
        try {
            $queryParams = $request->getQueryParams();
            $forceRefresh = isset($queryParams['refresh']) && $queryParams['refresh'] === 'true';
            
            $quoteModel = new \App\Models\QuoteOfTheDay();
            $result = $quoteModel->getQuoteOfTheDay($forceRefresh);
            
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