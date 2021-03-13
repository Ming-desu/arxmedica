<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\RecordExistsException;
use App\Helpers\JwtHelper;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\UserRepository;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class AuthController extends BaseController
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
   * @var AuthRepository
   */
  private $authRepository;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * @var Messages
   */
  private $flash;

  /**
   * AuthController Constructor
   * 
   * @var LoggerInterface $logger
   * @var UserRepository $userRepository
   * @var AuthRepository $authRepository
   * @var Twig $twig
   * @var Messages $messages
   */
  public function __construct(LoggerInterface $logger, UserRepository $userRepository, AuthRepository $authRepository, Twig $twig, Messages $flash)
  {
    $this->logger = $logger;
    $this->userRepository = $userRepository;
    $this->authRepository = $authRepository;
    $this->twig = $twig;
    $this->flash = $flash;
  }

  /**
   * Sign In View
   * 
   * @var Request $request
   * @var Response $response
   * @return Response
   */
  public function signIn(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from AuthController::index started');
    return $this->twig->render($response, 'pages/sign-in.html.twig');
  }

  /**
   * Sign Up View
   * 
   * @var Request $request
   * @var Response $response
   * @return Response
   */
  public function signUp(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from AuthController::signUp started');
    return $this->twig->render($response, 'pages/sign-up.html.twig');
  }

  /**
   * Sign in action, login an account
   * 
   * @var Request $request
   * @var Response $response
   * @throws InvalidCredentialsException
   * @return Response
   */
  public function login(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from AuthController::login started');

    // Set validation rule
    $v = new Validation($request->getParsedBody());
    $v->setRules([
      'username' => [
        'rules' => 'trim|required'
      ],
      'password' => [
        'rules' => 'trim|required'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $user = $this->authRepository->doLogin(new User($v->getData()));

    // Encodes some information about the user who logged in
    $jwt = new JwtHelper();
    $newResponse = $jwt->setCookieHeaders($response, [
      'user' => [
        'id' => $user->id,
        'username' => $user->username,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name
      ]
    ]);

    return $this->respondWithData($newResponse, [
      'message' => 'Successfully logged in.',
      'sub' => [
        'user' => $user
      ]
    ]);
  }

  /**
   * Logs out the current user active
   * 
   * @var Request $request
   * @var Response $response
   * @return Response
   */
  public function logout(Request $request, Response $response): Response
  {
    $jwt = new JwtHelper();
    $newResponse = $jwt->unsetCookieHeaders($response);
    $newResponse->getBody()->write(
      "
        Logging out please wait...
        <script>
          localStorage.removeItem('user');
          window.location.href = '{$_ENV['BASE_URL']}/login';
        </script>
      "
    );

    return $newResponse;
  }

  /**
   * Sign up action, store an account
   * 
   * @var Request $request
   * @var Response $response
   * @throws Exception
   * @throws RecordExistsException
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from AuthController::store started');

    // Set validation rule
    $v = new Validation($request->getParsedBody());
    $v->setRules([
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
        'rules' => 'trim|required'
      ]
    ]);

    if (!$v->runValidation())
      throw new Exception($v->getErrors()[0], 400);

    $user = $this->userRepository->create(new User($v->getData()));

    if ($user->status === 'inactive')
      $this->flash->addMessage('toast', 'Your account is inactive, wait for the admin to activate it.');

    return $this->respondWithData($response, [
      'message' => 'Successfully created an account.',
      'sub' => $user
    ]);
  }
}
