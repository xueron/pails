<?php
$autoloader = require __DIR__ . '/../src/composer_autoloader.php';

if (!$autoloader()) {
    die(
      'You need to set up the project dependencies using the following commands:' . PHP_EOL .
      'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
      'php composer.phar install' . PHP_EOL
    );
}

require __DIR__ . '/TestConfiguration.php';
