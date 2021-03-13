<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Repositories\AnalyticsRepository;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AnalyticsController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var AnalyticsRepository
   */
  private $analyticsRepository;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * Analytics Controller
   * 
   * @param LoggerInterface $logger
   * @param AnalyticsRepository $analyticsRepository
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, AnalyticsRepository $analyticsRepository, Twig $twig)
  {
    $this->logger = $logger;
    $this->analyticsRepository = $analyticsRepository;
    $this->twig = $twig;
  }

  /**
   * Index View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function index(Request $request, Response $response): Response
  {
    return $this->respondWithData($response, getenv('DATABASE_URL'));
    $this->logger->info('Dispatch from AnalyticsController::index started');
    $params = $request->getQueryParams();

    if (count($params) === 0)
      return $this->twig->render($response, 'pages/analytics/index.html.twig');

    $canvas = $this->analyticsRepository->get_cost_overview($params);

    return $this->respondWithData($response, [
      'sub' => $canvas
    ]);
  }

  /**
   * Print View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function print(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from AnalyticsController::print started');
    $params = $request->getQueryParams();

    $canvas = $this->analyticsRepository->get_cost_overview(array_merge($params, ['mode' => 'all']));

    if (count($canvas) === 0)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/analytics/print.html.twig', ['canvas' => $canvas]);
  }
}
