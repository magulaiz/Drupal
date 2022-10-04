<?php

namespace Drupal\options\Plugin\views\argument;

/**
 * A trait containing a helper method for title query for list fields.
 */
trait ListFieldTitleQueryTrait {

  /**
   * {@inheritdoc}
   */
  public function titleQuery() {
    return array_map(function ($key) {
      return $this->allowedValues[$key] ?? $key;
    }, $this->value);
  }

}
