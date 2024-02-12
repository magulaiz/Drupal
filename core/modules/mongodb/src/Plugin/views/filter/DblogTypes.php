<?php

namespace Drupal\mongodb\Plugin\views\filter;

/**
 * Overriding the views filter plugin "dblog_types".
 */
class DblogTypes extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = _dblog_get_message_types();
    }
    return $this->valueOptions;
  }

}
