<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class SettingsController extends BaseController
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
   * SettingsController Constructor
   * 
   * @var LoggerInterface $logger
   * @var Twig $twig
   */
  public function __construct(LoggerInterface $logger, Twig $twig)
  {
    $this->logger = $logger;
    $this->twig = $twig;
  }

  /**
   * Index View
   * 
   * @var Request $request
   * @var Response $response
   * @return Response
   */
  public function index(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from SettingsController::index started');
    return $this->twig->render($response, 'pages/settings/index.html.twig');
  }
}