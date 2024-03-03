<?php

namespace Drupal\entity_test;

use Drupal\Core\Field\FieldItemList;

/**
 * Overrides StringItem.
 */
class CustomStringItemList extends FieldItemList {

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE): void {
    if ($this->getEntity()->isNew() && $this->getName() === 'name') {
      $values .= ' (new)';
    }
    parent::setValue($values, $notify);
  }

}
