<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Models\StoreProduct;
use App\Repositories\StoreProductRepository;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class StoreProductsController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var StoreProductRepository
   */
  private $storeProductRepository;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * StoreProductsController Constructor
   * 
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger, StoreProductRepository $storeProductRepository, Twig $twig)
  {
    $this->logger = $logger;
    $this->storeProductRepository = $storeProductRepository;
    $this->twig = $twig;
  }

  /**
   * Read action, reads record from the database
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function index(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoreProductsController::index started');

    $route = RouteContext::fromRequest($request);
    $store_id = $route->getRoute()->getArgument('id');

    $params = $request->getQueryParams();
    $storeProducts = $this->storeProductRepository->read($params, intval($store_id));

    return $this->respondWithData($response, [
      'sub' => $storeProducts
    ]);
  }

  /**
   * Store action, push record to the database
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoreProductsController::store started');

    // Set validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'product_id' => [
        'rules' => 'trim|required'
      ],
      'unit_cost' => [
        'rules' => 'trim|required|decimal'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $store_product = $this->storeProductRepository->create(new StoreProduct($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully created a store product.',
      'sub' => $store_product
    ]);
  }

  /**
   * Update action, updates record to the database
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoreProductsController::update started');

    // Set validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'unit_cost' => [
        'rules' => 'trim|required|decimal'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $store_product = $this->storeProductRepository->update(new StoreProduct($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a store product.',
      'sub' => $store_product
    ]);
  }

  /**
   * Delete action, deletes record to the database
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoreProductsController::delete started');
    $store_product = $this->storeProductRepository->delete(new StoreProduct($request->getParsedBody()));

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a store product.',
      'sub' => $store_product
    ]);
  }
}
