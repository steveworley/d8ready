<?php
define('ROOT', __DIR__);

require 'vendor/autoload.php';

$command = "D8Ready\\" . $argv[1];

if (php_sapi_name() != 'cli') {
  exit('Invalid access attempt');
}

if (!class_exists($command)) {
  exit('Unsupported command issued.');
}

$cmd = new $command;
$cmd->setArgs(array_splice($argv, 2));

$cmd->run();
