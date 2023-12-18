<?php

namespace Drupal\xhprof\XHProfLib\Parser;

use Drupal\xhprof\XHProfLib\Run;

class Parser extends BaseParser {

  /**
   * @param $run
   * @param $sort
   * @param $symbol
   */
  public function __construct(Run $run, $sort, $symbol) {
    parent::__construct($run, $sort, $symbol);

    $this->diff_mode = FALSE;
  }

  /**
   * @return array
   */
  public function parse() {
    if (!empty($this->symbol)) {
      $symbols = $this->trimRun($this->run->getSymbols(), $this->symbol);
    }
    else {
      $symbols = $this->run->getSymbols();
    }

    $data = $this->computeFlatInfo($symbols);

    // @TODO Remove function name sorting.
    if ($this->sort === 'fn') {
      // Functions are array keys.
      \krsort($data);
    }
    else {
      $metric = $this->sort;
      \uasort($data, function ($a, $b) use ($metric) {
        // Arguments swapped to get more expensive values first.
        return $b[$metric] <=> $a[$metric];
      });
    }

    return $data;
  }

}
