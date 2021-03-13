<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class CategoriesController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var CategoryRepository
   */
  private $categoryRepository;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * CategoriesController Constructor
   * 
   * @param LoggerInterface $logger
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, CategoryRepository $categoryRepository, Twig $twig)
  {
    $this->logger = $logger;
    $this->categoryRepository = $categoryRepository;
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
    $this->logger->info('Dispatch from Categories::index started');
    $params = $request->getQueryParams();
    if (count($params) === 0)
      return $this->twig->render($response, 'pages/products/categories/index.html.twig');

    $categories = $this->categoryRepository->read($params);

    return $this->respondWithData($response, [
      'sub' => $categories
    ]);
  }

  /**
   * Create category, store action
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from Categories::store started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'name' => [
        'rules' => 'trim|required'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $category = $this->categoryRepository->create(new Category($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully created a category.',
      'sub' => $category
    ]);
  }

  /**
   * Update category
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from Categories::update started');

    // Set the validation rules
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'name' => [
        'rules' => 'trim|required'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $category = $this->categoryRepository->update(new Category($v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a category.',
      'sub' => $category
    ]);
  }

  /**
   * Delete category
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from CategoriesController::delete started');
    $category = $this->categoryRepository->delete(new Category($request->getParsedBody()));

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a category.',
      'sub' => $category
    ]);
  }
}
