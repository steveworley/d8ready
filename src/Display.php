<?php

namespace D8Ready;

use League\Csv\Reader;

class Display {
  protected $src;

  public function __construct($tpl) {
    $this->tpl = $tpl;
  }

  public function setSource($src) {
    if (!file_exists($src)) {
      throw new \InvalidArgumentException('Source file not found');
    }

    $this->src = $src;
    return $this;
  }

  public function render() {
    $reader = Reader::createFromPath($this->src);
    $headers = ['Module Name', 'Drupal 7', 'Drupal 8', 'URL'];

    echo $this->tpl->render('index.html', [
      'headers' => $headers,
      'list' => $reader,
    ]);
  }
}
