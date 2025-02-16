<?php

namespace Drupal\datetime\Plugin\views\field;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\Date as NumericDate;
use Drupal\views\ResultRow;

/**
 * Date/time views field.
 *
 * Even though dates are stored as strings, the numeric field is extended
 * because it provides more sensible operators.
 *
 * @ingroup views_field_handlers
 */
#[ViewsField("datetime")]
class Date extends NumericDate {

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL): ?int {
    $value = parent::getValue($values, $field);

    if (isset($value)) {
      $date = new DrupalDateTime($value);
      return $date->getTimestamp();
    }

    return NULL;
  }

}
