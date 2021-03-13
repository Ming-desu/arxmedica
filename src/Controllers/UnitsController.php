<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class UnitsController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var UnitRepository
   */
  private $unitRepository;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * CategoriesController Constructor
   * 
   * @param LoggerInterface $logger
   * @param UnitRepository $unitRepository
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, UnitRepository $unitRepository, Twig $twig)
  {
    $this->logger = $logger;
    $this->unitRepository = $unitRepository;
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
    $this->logger->info('Dispatch from UnitsController::index started');
    $params = $request->getQueryParams();
    if (count($params) === 0)
      return $this->twig->render($response, 'pages/products/units/index.html.twig');

    $units = $this->unitRepository->read($params);

    return $this->respondWithData($response, [
      'sub' => $units
    ]);
  }

  /**
   * Create unit, store action
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UnitsController::store started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'name' => [
        'rules' => 'trim|required|alpha'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $unit = $this->unitRepository->create(new Unit($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully created a unit.',
      'sub' => $unit
    ]);
  }

  /**
   * Update unit
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UnitsController::update started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'name' => [
        'rules' => 'trim|required|alpha'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $unit = $this->unitRepository->update(new Unit($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a unit.',
      'sub' => $unit
    ]);
  }

  /**
   * Delete unit
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UnitsController::delete started');
    $unit = $this->unitRepository->delete(new Unit($request->getParsedBody()));

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a unit.',
      'sub' => $unit
    ]);
  }
}
