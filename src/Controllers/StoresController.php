<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Repositories\StoreRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class StoresController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var StoreRepository
   */
  private $storeRepository;

  /**
   * @var Messages
   */
  private $flash;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * @var Validation
   */
  private $v;

  /**
   * StoresController Constructor
   * 
   * @param LoggerInterface $logger
   * @param StoreRepository $storeRepository
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, StoreRepository $storeRepository, Messages $flash, Twig $twig)
  {
    $this->logger = $logger;
    $this->storeRepository = $storeRepository;
    $this->flash = $flash;
    $this->twig = $twig;

    $this->v = new Validation([], $this->getRules());
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
    $this->logger->info('Dispatch from StoresController::index started');
    $params = $request->getQueryParams();
    if (count($params) === 0)
      return $this->twig->render($response, 'pages/stores/index.html.twig');

    $stores = $this->storeRepository->read($params);

    return $this->respondWithData($response, [
      'sub' => $stores
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
    $this->logger->info('Dispatch from StoresController::create started');
    return $this->twig->render($response, 'pages/stores/create.html.twig');
  }

  /**
   * Create store, store action
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoresController::store started');

    // Sets the data for the validation
    $this->v->setData($request->getParsedBody());

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $store = $this->storeRepository->create($this->v->getData());

    return $this->respondWithData($response, [
      'message' => 'Successfully created a store.',
      'sub' => $store
    ]);
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
    $this->logger->info('Dispatch from StoresController::edit started');
    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $store = $this->storeRepository->find(intval($id));

    if (!$store)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/stores/edit.html.twig', ['store' => $store->jsonSerialize()]);
  }

  /**
   * Update action, updates record in the database
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoresController::update started');

    // Sets the data for the validation
    $this->v->setData($request->getParsedBody());

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $store = $this->storeRepository->update($this->v->getData());

    $this->flash->addMessage('toast', 'Successfully updated a store.');

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a store.',
      'sub' => $store
    ]);
  }

  /**
   * Store View view
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function view(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoresController::view started');

    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $store = $this->storeRepository->find(intval($id));

    if (!$store)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/stores/view.html.twig', ['store' => $store->jsonSerialize()]);
  }

  /**
   * Delete action, deletes record from the database
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from StoresController::delete started');

    $store = $this->storeRepository->delete($request->getParsedBody());

    $this->flash->addMessage('toast', 'Successfully deleted a store.');

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a store.',
      'sub' => $store
    ]);
  }

  /**
   * The rules of the validation
   * 
   * @return array
   */
  private function getRules(): array
  {
    return array(
      'first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'contact_number' => [
        'rules' => 'optional|trim|integer'
      ],
      'name' => [
        'rules' => 'trim|required|special'
      ],
      'street' => [
        'rules' => 'trim|required|special'
      ],
      'municipality' => [
        'rules' => 'trim|required|alpha'
      ],
      'province' => [
        'rules' => 'trim|required|alpha'
      ]
    );
  }
}
