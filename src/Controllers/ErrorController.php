<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Base\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class ErrorController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * ErrorController Constructor
   * 
   * @param LoggerInterface $logger
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, Twig $twig)
  {
    $this->logger = $logger;
    $this->twig = $twig;
  }

  /**
   * Index Views
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function index(Request $request, Response $response): Response
  {
    return $this->twig->render($response, 'pages/404.html.twig');
  }
}
