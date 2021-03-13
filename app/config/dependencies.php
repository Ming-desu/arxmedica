<?php
declare(strict_types=1);

use App\Helpers\TwigHelper;
use atk4\dsql\Mysql\Connection;
use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use Twig\TwigFilter;

return function(ContainerBuilder $builder) {
  $builder->addDefinitions([
    LoggerInterface::class => function (ContainerInterface $c) {
      $settings = $c->get('settings');

      $loggerSettings = $settings['logger'];
      $logger = new Logger($loggerSettings['name']);

      $processor = new UidProcessor();
      $logger->pushProcessor($processor);

      $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
      $logger->pushHandler($handler);

      return $logger;
    },
    Connection::class => function (ContainerInterface $c) {
      $db = $c->get('settings')['db'];
      return Connection::connect($db['dsn'], $db['username'], $db['password']);
    },
    Messages::class => function() {
      // Array as default storage
      // Later the storage will be changed to $_SESSION
      $storage = [];

      return new Messages($storage);
    },
    Twig::class => function(ContainerInterface $c) {
      $twig = Twig::create(__DIR__ . '/../views');

      $environment = $twig->getEnvironment();
      $filter = new TwigFilter('currency', function($num) {
        $ones_arr = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        $tens_arr = ['ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        $specials_arr = ['eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $suffix_arr = ['hundred', 'thousand', 'million', 'billion', 'trillion', 'quadrillion'];

        $num = number_format($num, 2, ".", ",");

        $numbers = explode('.', $num);
        $whole_numbers = $numbers[0];
        $decimal_numbers = $numbers[1];

        $whole_numbers_arr = explode(',', $whole_numbers);

        $text = "";
        $suffix_index = count($whole_numbers_arr) - 1;
        foreach ($whole_numbers_arr as $key => $value) {
            $tens = floor($value % 100);
            $hundreds = floor($value % 1000 / 100);

            if ($hundreds > 0) 
                $text .= $ones_arr[$hundreds] . ' hundred ';

            if ($tens < 10) 
                $text .= $ones_arr[$tens];
            else if ($tens > 10 && $tens < 20)
                $text .= $specials_arr[substr(strval($tens), -1) - 1];
            else 
                $text .= $tens_arr[substr(strval($tens), 0, 1) - 1] . ' ' . $ones_arr[substr(strval($tens), -1)];

            $text .= $suffix_index > 0 ? ' ' . $suffix_arr[$suffix_index] . ' ' : '';
            $suffix_index--;
        }

        $text .= " peso(s) ";

        if ($decimal_numbers > 0) {
            $text .= " and ";

            if ($decimal_numbers < 10) 
                $text .= $ones_arr[$decimal_numbers];
            else if ($decimal_numbers > 10 && $decimal_numbers < 20)
                $text .= $specials_arr[substr(strval($decimal_numbers), -1) - 1];
            else 
                $text .= $tens_arr[substr(strval($decimal_numbers), 0, 1) - 1] . ' ' . $ones_arr[substr(strval($decimal_numbers), -1)];

            $text .= " centavo(s) ";
        }

        $text .= " only ";

        return ucwords($text);
      });

      $environment->addFilter($filter);
      $environment->addGlobal('flash', $c->get(Messages::class));
      
      return $twig;
    }
  ]);
};