<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
  public $code = 400;
  public $message = 'Invalid username or password.';
}