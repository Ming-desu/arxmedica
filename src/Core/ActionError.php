<?php
declare(strict_types=1);

namespace App\Core;

use JsonSerializable;

class ActionError implements JsonSerializable
{
  public const BAD_REQUEST = 'BAD_REQUEST';
  public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
  public const NOT_ALLOWED = 'NOT_ALLOWED';
  public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
  public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
  public const SERVER_ERROR = 'SERVER_ERROR';
  public const UNAUTHENTICATED = 'UNAUTHENTICATED';

  /**
   * @var string
   */
  private $type;

  /**
   * @var string
   */
  private $description;

  public function __construct(string $type, ?string $description)
  {
    $this->type = $type;
    $this->description = $description;
  }

  public function jsonSerialize(): array
  {
    $payload = [
      'type' => $this->type,
      'description' => $this->description
    ];

    return $payload;
  }

  /**
   * Gets the type
   * 
   * @return string
   */
  public function getType(): string 
  {
    return $this->type;
  }

  /**
   * Sets the type
   * 
   * @param string $type
   * @return self
   */
  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  /**
   * Gets the description
   * 
   * @return string
   */
  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * Sets the description
   * 
   * @param string|null $description
   * @return self
   */
  public function setDescription(?string $description = null): self
  {
    $this->description = $description;
    return $this;
  }
}