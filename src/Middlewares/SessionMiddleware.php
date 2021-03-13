<?php
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;

class SessionMiddleware
{
  /**
   * @var Messages
   */
  private $flash;

  public function __construct(Messages $flash)
  {
    $this->flash = $flash;
  }

  /**
   * @var Request
   * @var RequestHandler
   * @return Response
   */
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    // if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    //   session_start();
    //   $request = $request->withAttribute('session', $_SESSION);
    // }

    // Check that the session hasn't already been started
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
      session_start();
    }

    // Change the storage
    $this->flash->__construct($_SESSION);

    return $handler->handle($request);
  }
}