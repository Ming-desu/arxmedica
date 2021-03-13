<?php
declare(strict_types=1);

namespace App\Helpers;

class StringHelper
{
  /**
   * Converts snake case to pascal case
   * some_string = Some String
   * 
   * @param string $string
   * @return string
   */
  public static function snakeCaseToPascalCase(string $string): string
  {
    return ucwords(implode(' ', explode('_', $string)));
  }
}