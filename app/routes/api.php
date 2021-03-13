<?php
declare(strict_types=1);

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function(App $app) {
  $app->group('/api', function(Group $group) {
    $group->get('', function(Request $request, Response $response) {
      $secret = bin2hex(random_bytes(16));
      $response->getBody()->write('Hello from API route' . phpinfo());
      return $response;
    });
  });
};