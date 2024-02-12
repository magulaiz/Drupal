<?php

namespace Drupal\mongodb\Plugin\views\field;

use Drupal\history\Plugin\views\field\HistoryUserTimestamp as CoreHistoryUserTimestamp;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use MongoDB\BSON\UTCDateTime;

/**
 * Overriding the views field plugin "history_user_timestamp".
 */
class HistoryUserTimestamp extends CoreHistoryUserTimestamp {

  use FieldPluginTrait;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    if (\Drupal::currentUser()->isAuthenticated()) {
      $this->additional_fields['created'] = ['table' => 'node', 'field' => 'created'];
      $this->additional_fields['changed'] = ['table' => 'node', 'field' => 'changed'];
      if (\Drupal::moduleHandler()->moduleExists('comment') && !empty($this->options['comments'])) {
        $this->additional_fields['last_comment'] = ['table' => 'comment_entity_statistics', 'field' => 'last_comment_timestamp'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Let's default to 'read' state.
    // This code shadows node_mark, but it reads from the db directly and
    // we already have that info.
    $mark = MARK_READ;
    if (\Drupal::currentUser()->isAuthenticated()) {
      $last_read = $this->getValue($values);
      if ($last_read instanceof UTCDateTime) {
        $last_read = (int) $last_read->__toString();
        $last_read = $last_read / 1000;
        $last_read = (string) $last_read;
      }
      $changed = $this->getValue($values, 'changed');

      $last_comment = \Drupal::moduleHandler()->moduleExists('comment') && !empty($this->options['comments']) ? $this->getValue($values, 'last_comment') : 0;

      if (!$last_read && $changed > HISTORY_READ_LIMIT) {
        $mark = MARK_NEW;
      }
      elseif ($changed > $last_read && $changed > HISTORY_READ_LIMIT) {
        $mark = MARK_UPDATED;
      }
      elseif ($last_comment > $last_read && $last_comment > HISTORY_READ_LIMIT) {
        $mark = MARK_UPDATED;
      }
      $build = [
        '#theme' => 'mark',
        '#status' => $mark,
      ];
      return $this->renderLink(\Drupal::service('renderer')->render($build), $values);
    }
  }

}
