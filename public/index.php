<?php

declare(strict_types=1);

use App\Core\Emitters\ResponseEmitter;
use App\Core\Handlers\HttpErrorHandler;
use App\Core\Handlers\ShutdownHandler;
use App\Middlewares\JsonBodyParserMiddleware;
use App\Middlewares\RemoveMethodOverrideMiddleware;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Middleware\MethodOverrideMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('./../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();

// TODO:: Should be true in production
if (false) {
    $containerBuilder->enableCompilation('./../var/cache');
}

// Adds all the dependencies needed in container
$settings = require __DIR__ . '/../app/config/settings.php';
$settings($containerBuilder);

// Register dependencies to the container
$dependencies = require __DIR__ . '/../app/config/dependencies.php';
$dependencies($containerBuilder);

// Register repositories to the container
$repositories = require __DIR__ . '/../app/config/repositories.php';
$repositories($containerBuilder);

// Builds the container
$container = $containerBuilder->build();

// Determine whether to display error details or not
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Creates the instance of Slim App
$app = Bridge::create($container);

// Register middlewares
$middleware = require __DIR__ . '/../app/config/middlewares.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/config/routes.php';
$routes($app);

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add routing middleware
$app->addRoutingMiddleware();

// Add method override middleware
$methodOverrideMiddleware = new MethodOverrideMiddleware();
$app->add($methodOverrideMiddleware);

// Add json body parser middleware
$jsonBodyParserMiddleware = new JsonBodyParserMiddleware();
$app->add($jsonBodyParserMiddleware);

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
