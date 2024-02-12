<?php

namespace Drupal\mongodb\Plugin\views\filter;

use Drupal\tracker\Plugin\views\filter\UserUid;

/**
 * Overriding the views filter plugin "tracker_user_uid".
 */
class TrackerUserUid extends UserUid {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Because this handler thinks it's an argument for a field on the {node}
    // table, we need to make sure {tracker_user} is JOINed and use its alias
    // for the WHERE clause.
    $tracker_user_alias = $this->query->ensureTable('tracker_user');
    // Cast scalars to array so we can consistently use an IN condition.
    $this->value = (array) $this->value;
    foreach ($this->value as &$value) {
      $value = (int) $value;
    }
    $this->query->addCondition(0, "$tracker_user_alias.uid", $this->value, 'IN');
  }

}
