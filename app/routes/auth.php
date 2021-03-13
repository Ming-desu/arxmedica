<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function(App $app) {
  $app->group('/auth', function(Group $auth) {
    $auth->post('', AuthController::class . '::login')->setName('auth.login');
    $auth->get('/logout', AuthController::class . '::logout');
  });
};