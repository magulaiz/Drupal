<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\tracker\Plugin\views\argument\UserUid;

/**
 * Overriding the views argument plugin "tracker_user_uid".
 */
class TrackerUserUid extends UserUid {

  use CommentUserUidTrait;

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    // Because this handler thinks it's an argument for a field on the {node}
    // table, we need to make sure {tracker_user} is JOINed and use its alias
    // for the WHERE clause.
    $tracker_user_alias = $this->query->ensureTable('tracker_user');
    $this->query->addCondition(0, "$tracker_user_alias.uid", (int) $this->argument);
  }

}
