<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Cookies;

class JwtHelper
{
  /**
   * @var array
   */
  private $payload = [
    'iss' => '',
    'aud' => 'http://localhost',
  ];

  /**
   * JwtHelper Constructor
   */
  public function __construct()
  {
    $this->payload['iss'] = $_ENV['BASE_URL'];
    $this->setExp();
  }

  /**
   * Sets the expiration of token
   * 
   * @param int $time
   * @return self
   */
  public function setExp(int $time = 60 * 15): self 
  {
    $this->payload['iat'] = time();
    $this->payload['nbf'] = time();
    $this->payload['exp'] = time() + $time;
    return $this;
  }

  /**
   * Encodes to JWT Token
   * 
   * @param mixed $sub
   * @return string
   */
  public function encode($sub): string 
  {
    $this->payload['sub'] = $sub;
    return JWT::encode($this->payload, $_ENV['SECRET'], 'HS256');
  }

  /**
   * Decodes the JWT to its original value
   * 
   * @param string $token
   * @return mixed
   */
  public function decode(string $token)
  {
    return JWT::decode($token, $_ENV['SECRET'], ['HS256']);
  }

  /**
   * Sets Cookie header to the ResponseInterface
   * 
   * @param ResponseInterface $response
   * @param mixed $sub
   * @return ResponseInterface
   */
  public function setCookieHeaders(ResponseInterface $response, $sub): ResponseInterface
  {
    // Encodes some information
    $token = $this->setExp()->encode($sub);

    // Generate refresh token
    $refresh_token = $this->generateRefreshToken();
    // Expires in 7 days
    $expires = 60 * 60 * 24 * 7;

    // Sets the cookie in the headers
    $cookies = new Cookies();
    $cookies->set('refresh_token', [
      'value' => $refresh_token,
      'expires' => time() + $expires,
      'path' => '/',
      'httponly' => true
    ]);
    $cookies->set('token', [
      'value' => $token,
      'expires' => time() + $expires,
      'path' => '/',
      'httponly' => true
    ]);

    $response = $response->withHeader('Set-Cookie', $cookies->toHeaders());

    return $response;
  }

  /**
   * Unsets Cookie header to the ResponseInterface
   * 
   * @param ResponseInterface $response
   * @return ResponseInterface
   */
  public function unsetCookieHeaders(ResponseInterface $response): ResponseInterface
  {
    $response = $response
      ->withHeader('Set-Cookie', [
        "refresh_token=expired; Max-Age=-3600; Path=/; HttpOnly",
        "token=expired; Max-Age=-3600; Path=/; HttpOnly"
      ]);

    return $response;
  }

  /**
   * Generate cyptographically strong string
   * 
   * @return string
   */
  public function generateRefreshToken(): string
  {
    return bin2hex(random_bytes(16));
  }
}