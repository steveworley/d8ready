<?php
define(ROOT, __DIR__ . '/../');
require  ROOT . '/vendor/autoload.php';

use D8Ready\Scraper;
use D8Ready\Display;

// Bootstrap all the things.
$loader = new Twig_Loader_Filesystem(ROOT . '/lib/tpl');
$twig = new Twig_Environment($loader);

// Build the display.
$app = new Display($twig);

try {
  $app->setSource(ROOT. '/lib/results.csv');
  $app->render();
}
catch (\Exception $error) {
  echo $twig->render('error.html', ['message' => $error]);
}
