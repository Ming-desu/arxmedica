<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class RecordExistsException extends Exception 
{
  public $code = 400;
}