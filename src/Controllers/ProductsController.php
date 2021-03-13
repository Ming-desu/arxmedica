<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class ProductsController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var ProductRepository
   */
  private $productRepository;

  /**
   * @var Messages
   */
  private $flash;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * ProductsController Constructor
   * 
   * @param LoggerInterface $logger
   * @param ProductRepository $productRepository
   * @param Messages $flash
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, ProductRepository $productRepository, Messages $flash, Twig $twig)
  {
    $this->logger = $logger;
    $this->productRepository = $productRepository;
    $this->flash = $flash;
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
    $this->logger->info('Dispatch from ProductsController::index started');
    $params = $request->getQueryParams();
    if (count($params) === 0)
      return $this->twig->render($response, 'pages/products/index.html.twig');

    $products = $this->productRepository->read($params);

    return $this->respondWithData($response, [
      'sub' => $products
    ]);
  }

  /**
   * Create View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from ProductsController::create started');
    return $this->twig->render($response, 'pages/products/create.html.twig');
  }

  /**
   * Edit View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function edit(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from ProductsController::edit started');

    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $product = $this->productRepository->find(intval($id));

    if (!$product)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/products/edit.html.twig', ['product' => $product->jsonSerialize()]);
  }

  /**
   * Create product, store action
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from ProductsController::store started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules($this->getRules());

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $product = $this->productRepository->create(new Product($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully created a product.',
      'sub' => $product
    ]);
  }

  /**
   * Update product
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from ProductsController::update started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules($this->getRules());

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $product = $this->productRepository->update(new Product($v->getData()));
    $this->flash->addMessage('toast', 'Successfully updated a product.');

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a product.',
      'sub' => $product
    ]);
  }

  /**
   * Deletes product
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from ProductsController::delete started');
    $product = $this->productRepository->delete(new Product($request->getParsedBody()));

    $this->flash->addMessage('toast', 'Successfully deleted a product.');

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a product.',
      'sub' => $product
    ]);
  }

  /**
   * Returns the rules of the validation
   * 
   * @return array
   */
  private function getRules(): array
  {
    return [
      'category_id' => [
        'rules' => 'trim|required'
      ],
      'unit_id' => [
        'rules' => 'trim|required'
      ],
      'brand' => [
        'rules' => 'optional|trim|special'
      ],
      'description' => [
        'rules' => 'trim|required|special'
      ]
    ];
  }
}
