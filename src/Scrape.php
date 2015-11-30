<?php

namespace D8Ready;

use League\Csv\Writer;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Colors\Color;

class Scrape {

  protected $token = 'token.inc';

  protected $search = 'https://www.google.com.au/search?q=site:drupal.org%2Fproject';

  public function __construct() {
    $this->client = new Client();
  }

  public function setLimit($last = 0) {
    $token = fopen($this->token, 'w');
    fwrite($token, $last);
  }

  public function getLimit() {
    return file_exists($this->token) ? trim(file_get_contents($this->token)) : 0;
  }

  public function setArgs($args = []) {
    $this->args = [];
    return;
  }

  public function run() {
    $c = new Color;
    $crawler = new Crawler();
    $response = $this->client->request('GET', $this->search . '&start=' . $this->getLimit());
    $crawler->addContent((string) $response->getBody());

    $data = file_get_contents(ROOT . '/lib/results.csv');

    foreach ($crawler->filter('cite') as $url) {
      $row = ['name' => '', 'd7' => '', 'd8' => '', 'url' => ''];
      echo "Found: {$url->nodeValue}" . PHP_EOL;

      // Attempt to make a request to D.O to get details about the module.
      $res = $this->client->request('GET', $url->nodeValue);

      if ($res->getStatusCode() > 400) {
        echo $c("Unable to fetch data")->red() . PHP_EOL;
        continue;
      }

      $body = (string) $res->getBody();

      if (empty($body)) {
        echo $c('Unable to fetch body')->red() . PHP_EOL;
        continue;
      }

      $crawl = new Crawler();
      $crawl->addContent($res->getBody());

      // Add the known elements.
      $row['name'] = trim($crawl->filter('#page-subtitle')->text());
      $row['url'] = trim($url->nodeValue);

      // The help block often has information about the status to D8.
      if (count($crawl->filter('.help'))) {
        $help = $crawl->filter('.help')->text();
        if (strpos($help, 'ported to Drupal 8') > -1) {
          $row['d8'] = 'In progress';
        }
      }

      foreach ($crawl->filter('[data-th="Version"]') as $version) {
        $version = $version->nodeValue;

        if (strpos($version, '7.x') > -1) {
          $row['d7'] = trim($version);
          continue;
        }

        if (strpos($version, '8.x') > -1) {
          $row['d8'] = trim($verison);
          continue;
        }
      }

      // This module hasn't been ported to D7 - so continue.
      if (empty($row['d7']) && empty($row['d8'])) {
        echo $c('<bg_yellow>This is a Drupal 6 module</bg_yellow>')->colorize() . PHP_EOL;
        continue;
      }

      $data .= implode(',', array_values($row)) . "\n";
      echo $c('Successfully added metadata')->green() . PHP_EOL;
    }

    $h = fopen(ROOT . '/lib/results.csv', 'w');
    fwrite($h, $data);

    // Increment the limit.
    $limit = $this->getLimit() + 10;
    echo $c('Updating the limit from <yellow>' . $this->getLimit() . '</yellow> to <yellow>' . $limit . '</yellow>')->colorize() . PHP_EOL;
    $this->setLimit($limit);
  }

}
