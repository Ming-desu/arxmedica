<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Helpers\AuthHelper;
use App\Helpers\JwtHelper;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\UserRepository;
use Firebase\JWT\ExpiredException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;

class AuthMiddleware
{
  /**
   * @var AuthRepository
   */
  private $authRepository;

  /**
   * @var UserRepository
   */
  private $userRepository;

  /**
   * @var Messages
   */
  private $flash;

  /**
   * @var JwtHelper
   */
  private $jwt;

  /**
   * AuthMiddleware Constructor
   * 
   * @param AuthRepository $authRepository
   */
  public function __construct(AuthRepository $authRepository, UserRepository $userRepository, Messages $flash)
  {
    $this->authRepository = $authRepository;
    $this->userRepository = $userRepository;
    $this->flash = $flash;
    $this->jwt = new JwtHelper();  
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $response = $handler->handle($request);
    $route = trim(RouteContext::fromRequest($request)->getRoute()->getPattern(), '/');

    // Check either the token or the refresh token is missing
    if (!isset($_COOKIE['token']) || !isset($_COOKIE['refresh_token'])
    ) {
      $newResponse = $response
        ->withHeader('Location', $_ENV['BASE_URL'] . '/login');
      
      return $newResponse;
    }

    // Gets the current user
    $user = AuthHelper::getCurrentUser();

    // Resources that does not need any permission to access it.
    $public_pages = [
      'analytics',
      'profile',
      'request'
    ];

    // Check if the user has the permission
    // to access the requested page
    $permissions = explode(',', str_replace('inventory', 'products', $this->authRepository->getPermission(new User([
      'id' => $user->id
    ]))));
    $route = explode('/', $route)[0];

    // Check for the permission and exclude the values in $public_pages
    if (!in_array($route, $public_pages) && !in_array($route, $permissions)) {
      $this->flash->addMessage('toast', 'Your account do not have the permission to continue.');
      $newResponse = $response
        ->withHeader('Location', $_ENV['BASE_URL'] . '/analytics');
      
      return $newResponse;
    }

    // If the token is expired, request new one
    try {
      $user = $this->jwt->decode($_COOKIE['token']);
    }
    catch(ExpiredException $e) {
      $user = $this->userRepository->find(intval($user->id));
      $newResponse = $this->jwt->setCookieHeaders($response, [
        'user' => [
          'id' => $user->id,
          'username' => $user->username,
          'first_name' => $user->first_name,
          'last_name' => $user->last_name
        ]
      ]);

      return $newResponse;
    }

    return $response;
  }
}