<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RemoveMethodOverrideMiddleware
{
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $parsedBody = $request->getParsedBody();

    // Removes the method overwriter
    if (isset($parsedBody['_METHOD'])) {
      unset($parsedBody['_METHOD']);
    }

    $request = $request->withParsedBody($parsedBody);

    return $handler->handle($request);
  }
}
