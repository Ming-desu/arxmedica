<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Middlewares\RemoveMethodOverrideMiddleware;
use App\Middlewares\RouteNameMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->redirect('/', '/analytics');

    $page = require __DIR__ . '/../routes/pages.php';
    $page($app);

    $auth = require __DIR__ . '/../routes/auth.php';
    $auth($app);

    $api = require __DIR__ . '/../routes/api.php';
    $api($app);

    $app->get('/{routes:.*}', ErrorController::class . '::index')->add(RouteNameMiddleware::class);

    $app->add(RemoveMethodOverrideMiddleware::class);
};
