<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Repositories\UserRepository;

class AuthHelper
{
  /**
   * Gets the currrent user login
   */
  public static function getCurrentUser()
  {
    return json_decode(base64_decode(explode('.', $_COOKIE['token'])[1]))->sub->user;
  }
}