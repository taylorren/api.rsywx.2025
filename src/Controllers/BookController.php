<?php

namespace App\Controllers;

use App\Models\BookStatus;
use App\Models\Book;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookController
{
    private $bookStatusModel;

    public function __construct()
    {
        $this->bookStatusModel = new BookStatus();
    }

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
}
