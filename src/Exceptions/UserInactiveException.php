<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class UserInactiveException extends Exception
{
  public $code = 401;
  public $message = 'User is currently not active, wait for the admin\'s approval.';
}