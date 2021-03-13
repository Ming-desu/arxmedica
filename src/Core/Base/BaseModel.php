<?php
declare(strict_types=1);

namespace App\Core\Base;

use atk4\dsql\Mysql\Connection;
use JsonSerializable;

class BaseModel implements JsonSerializable
{
  /**
   * @var array
   */
  protected $properties;

  /**
   * @var Connection
   */
  protected $c;
  
  /**
   * @var array
   */
  protected $hidden = [];

  /**
   * @var array
   */
  protected $ucWords = [];

  /**
   * @param array $properties
   */
  public function __construct(array $properties = [])
  {
    $this->properties = $properties;
  }

  /**
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value)
  {
    $this->properties[$name] = $value;
  }

  /**
   * @param mixed $name
   * @return mixed|null
   */
  public function __get($name)
  {
    if (isset($this->properties[$name]))
      return $this->properties[$name];
    return null;
  }

  /**
   * @param Connection $connection
   */
  public function setConnection(Connection $connection)
  {
    $this->c = $connection;
  }

  /**
   * @return array
   */
  public function getProperties(): array
  {
    array_walk($this->properties, function(&$value, $key) {
      $value = in_array($key, $this->ucWords) ? ucwords($value) : $value;
    });
    return $this->properties;
  }

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    return array_filter($this->properties, function($propertyName) {
      return !in_array($propertyName, $this->hidden);
    }, ARRAY_FILTER_USE_KEY);
  }
}