<?php

namespace Drupal\mongodb\Plugin\Search;

use Drupal\help\Plugin\Search\HelpSearch as CoreHelpSearch;

/**
 * Overriding the search plugin "help_search".
 */
class HelpSearch extends CoreHelpSearch {

  /**
   * {@inheritdoc}
   */
  public function indexStatus() {
    $this->updateTopicList();
    $total = $this->database->select('help_search_items', 'hsi')
      ->countQuery()
      ->execute()
      ->fetchField();

    $query = $this->database->select('help_search_items', 'hsi');
    $query->fields('hsi', ['sid']);
    $query->leftJoin('search_dataset', 'sd',
      $query->joinCondition()
        ->compare('hsi.sid', 'sd.sid')
        ->condition('sd.type', $this->getType())
    );
    $condition = $this->database->condition('OR');
    $condition->condition('sd.reindex', 0, '<>')
      ->isNull('sd.sid');
    $query->condition($condition);
    $results = $query->execute()->fetchCol();

    // Get the distinct sids.
    $distinct_sids = [];
    foreach ($results as $sid) {
      if (!isset($distinct_sids[$sid])) {
        $distinct_sids[$sid] = 1;
      }
    }

    return [
      'remaining' => count($distinct_sids),
      'total' => $total,
    ];
  }

}
