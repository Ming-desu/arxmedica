<?php
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class RouteNameMiddleware
{
  private $twig;

  public function __construct(Twig $twig)
  {
    $this->twig = $twig;
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    
    if ($route !== null)
      $this->twig->getEnvironment()->addGlobal('route_name', $route->getName());

    $this->twig->getEnvironment()->addGlobal('base_url', $_ENV['BASE_URL']);

    return $handler->handle($request);
  }
}