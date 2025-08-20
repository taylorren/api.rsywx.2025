<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class BaseTestCase extends TestCase
{
    protected $app;
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();

        // Load test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['API_KEY'] = 'test-api-key-12345';
        $_ENV['DB_HOST'] = 'lenovo';
        $_ENV['DB_NAME'] = 'rsywx';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASSWORD'] = 'Trgr0210$';
        $_ENV['DB_CHARSET'] = 'utf8mb4';
        $_ENV['API_VERSION'] = 'v1';

        // Create container and app
        $this->container = new Container();
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();

        // Add middleware (simplified for testing)
        $this->setupMiddleware();
        $this->setupRoutes();
    }

    protected function setupMiddleware()
    {
        // Add CORS middleware
        $this->app->add(function (Request $request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-API-Key')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        });

        // API Key middleware
        $this->app->add(function (Request $request, $handler) {
            $uri = $request->getUri()->getPath();

            // Skip auth for health check and docs
            if ($uri === '/' || $uri === '/health' || strpos($uri, '/api-docs') === 0) {
                return $handler->handle($request);
            }

            $apiKey = $request->getHeaderLine('X-API-Key') ?: $request->getQueryParams()['api_key'] ?? null;

            if (!$apiKey || $apiKey !== $_ENV['API_KEY']) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Invalid or missing API key'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            return $handler->handle($request);
        });
    }

    protected function setupRoutes()
    {
        // Health check
        $this->app->get('/health', function (Request $request, $response) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'API is running',
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        // API routes
        $this->app->group('/api/' . ($_ENV['API_VERSION'] ?? 'v1'), function ($group) {
            $group->get('/books/status', \App\Controllers\BookController::class . ':status');
            $group->get('/books/latest[/{count:[0-9]+}]', \App\Controllers\BookController::class . ':latest');
            $group->get('/books/random[/{count:[0-9]+}]', \App\Controllers\BookController::class . ':random');
            $group->get('/books/last_visited[/{count:[0-9]+}]', \App\Controllers\BookController::class . ':lastVisited');
            $group->get('/books/forgotten[/{count:[0-9]+}]', \App\Controllers\BookController::class . ':forgotten');
            $group->get('/books/today/{month:[0-9]+}/{date:[0-9]+}', \App\Controllers\BookController::class . ':todayWithParams');
            $group->get('/books/today', \App\Controllers\BookController::class . ':today');
            $group->get('/books/list[/{type}[/{value}[/{page}]]]', \App\Controllers\BookController::class . ':listBooks');
            $group->get('/books/{type}/{value}/{page}', \App\Controllers\BookController::class . ':listBooks');
            $group->get('/books/{bookid}', \App\Controllers\BookController::class . ':show');
            $group->get('/misc/wotd', \App\Controllers\MiscController::class . ':wordOfTheDay');
            $group->get('/misc/qotd', \App\Controllers\MiscController::class . ':qotd');
            $group->get('/misc/weather/current', \App\Controllers\MiscController::class . ':currentWeather');
            $group->get('/misc/weather/forecast', \App\Controllers\MiscController::class . ':weatherForecast');
            $group->get('/readings/summary', \App\Controllers\ReadingController::class . ':summary');
            $group->get('/readings/latest[/{count:[0-9]+}]', \App\Controllers\ReadingController::class . ':latest');
        });
    }

    protected function createRequest(string $method, string $uri, array $headers = [], array $data = [])
    {
        // Create request using Slim's factory
        $factory = new \Slim\Psr7\Factory\ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri);

        // Add headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Add body data for POST requests only
        if (!empty($data) && $method === 'POST') {
            $request->getBody()->write(json_encode($data));
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        return $request;
    }

    protected function runApp(Request $request)
    {
        return $this->app->handle($request);
    }
}