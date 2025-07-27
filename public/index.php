<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_ENV'] === 'development',
    true,
    true
);

// Add CORS middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-API-Key')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Handle preflight requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// API Key middleware
$app->add(function (Request $request, $handler) {
    $uri = $request->getUri()->getPath();
    
    // Skip auth for health check
    if ($uri === '/health') {
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

// Health check endpoint (no auth required)
$app->get('/health', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => date('Y-m-d H:i:s')
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// API routes
$app->group('/api/' . $_ENV['API_VERSION'], function ($group) {
    $group->get('/books/status', \App\Controllers\BookController::class . ':status');
    $group->get('/books/{bookid}', \App\Controllers\BookController::class . ':show');
});

$app->run();