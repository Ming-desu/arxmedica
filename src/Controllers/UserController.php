<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\RecordExistsException;
use App\Helpers\AuthHelper;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class UserController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var UserRepository
   */
  private $userRepository;

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
   * UserController Constructor
   * 
   * @param LoggerInterface $logger
   * @param UserRepository $userRepository
   * @param Messages $flash
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, UserRepository $userRepository, Messages $flash, Twig $twig)
  {
    $this->logger = $logger;
    $this->userRepository = $userRepository;
    $this->flash = $flash;
    $this->twig = $twig;
    $this->v = new Validation([]);
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
    $this->logger->info('Dispatch from UserController::index started');
    $params = $request->getQueryParams();
    if (count($params) === 0)
      return $this->twig->render($response, 'pages/accounts/index.html.twig');

    $users = $this->userRepository->read($params);

    return $this->respondWithData($response, [
      'sub' => $users
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
    $this->logger->info('Dispatch from UserController::create started');
    return $this->twig->render($response, 'pages/accounts/create.html.twig');
  }

  /**
   * Profile View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function profile(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::profile started');
    return $this->twig->render($response, 'pages/accounts/profile.html.twig');
  }

  /**
   * Request User Information
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function request(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::request started');

    // Gets the current userRepository
    $user = AuthHelper::getCurrentUser();
    $user = $this->userRepository->find(intval($user->id));

    return $this->respondWithData($response, [
      'sub' => [
        'user' => $user
      ]
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
    $this->logger->info('Dispatch from UserController::edit started');

    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $account = $this->userRepository->find(intval($id));

    if (!$account || $id === '1')
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/accounts/edit.html.twig', ['account' => $account->jsonSerialize()]);
  }

  /**
   * Create account, store action
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::store started');

    $this->v->setData($request->getParsedBody());
    $this->v->setRules([
      'first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'sex' => [
        'rules' => 'trim|required'
      ],
      'username' => [
        'rules' => 'trim|required|username'
      ],
      'password' => [
        'rules' => 'trim|optional|required'
      ]
    ]);

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $user = $this->userRepository->create(new User($this->v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully created an account.',
      'sub' => $user
    ]);
  }

  /**
   * Updates account
   * 
   * @param Request $request
   * @param Response $response
   * @return Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::update started');

    $this->v->setData($request->getParsedBody());
    $this->v->setRules([
      'first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'sex' => [
        'rules' => 'trim|required'
      ],
      'username' => [
        'rules' => 'trim|required|username'
      ],
      'password' => [
        'rules' => 'trim|optional|required'
      ],
      'status' => [
        'rules' => 'trim|required'
      ]
    ]);

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $user = $this->userRepository->update(new User($this->v->getData()));
    $this->flash->addMessage('toast', 'Successfully updated an account.');

    return $this->respondWithData($response, [
      'message' => 'Successfully updated an account.',
      'sub' => $user
    ]);
  }

  public function updateGeneral(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::updateGeneral started');

    $this->v->setData($request->getParsedBody());
    $this->v->setRules([
      'first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'sex' => [
        'rules' => 'trim|required'
      ]
    ]);

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $data = $this->v->getData();
    $data['files'] = $this->handleUploadedFiles($request->getUploadedFiles());

    $user = $this->userRepository->update(new User($data));

    return $this->respondWithData($response, [
      'message' => 'Successfully updated your personal information.',
      'sub' => $user
    ]);
  }

  public function updateCredentials(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::updateCredentials started');

    $this->v->setData($request->getParsedBody());
    $this->v->setRules([
      'username' => [
        'rules' => 'trim|required|username'
      ],
      'password' => [
        'rules' => 'trim|optional|required'
      ]
    ]);

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    $user = $this->userRepository->update(new User($this->v->getData()));

    return $this->respondWithData($response, [
      'message' => 'Successfully updated your credentials information.',
      'sub' => $user
    ]);
  }

  /**
   * Deletes account
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from UserController::delete started');
    $account = $this->userRepository->delete(new User($request->getParsedBody()));

    $this->flash->addMessage('toast', 'Successfully deleted an account.');

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted an account.',
      'sub' => $account
    ]);
  }
}
