<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function(ContainerBuilder $builder) {
  $builder->addDefinitions([
    'settings' => [
      'logger' => [
          'name' => 'slim-app',
          'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../var/logs/app.log',
          'level' => Logger::DEBUG,
      ],
      'displayErrorDetails' => true,
      'app_env' => 'DEVELOPMENT',
      'timezone' => 'Asia/Manila',
      'db' => [
        'dsn' => "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8",
        'username' => "{$_ENV['DB_USER']}",
        'password' => "{$_ENV['DB_PASS']}"
      ]
    ]
  ]);
};