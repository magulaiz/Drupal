<?php

/**
 * @file
 * Post update functions for Filter.
 */

use Drupal\Core\Site\Settings;
use Drupal\Core\Utility\Error;

/**
 * Sorts filter format filter configuration.
 */
function filter_post_update_sort_filters(?array &$sandbox = NULL): void {
  $factory = \Drupal::configFactory();
  $iteration_size = Settings::get('entity_update_batch_size', 50);

  if (empty($sandbox['progress'])) {
    $sandbox['progress'] = 0;
    $sandbox['config_names'] = $factory->listAll('filter.format.');
    $sandbox['max'] = count($sandbox['config_names']);
  }

  $start = $sandbox['progress'];
  $end = min($sandbox['max'], $start + $iteration_size);
  for ($i = $start; $i < $end; $i++) {
    try {
      $filter_format_config = $factory->getEditable($sandbox['config_names'][$i]);

      // Re-save the filter format if the order of filters changes after
      // sorting.
      $sorted_filters = $filters = array_keys($filter_format_config->get('filters'));
      sort($sorted_filters);
      if ($sorted_filters !== $filters) {
        $filter_format_config->save();
      }
    }
    catch (\Exception $e) {
      Error::logException(\Drupal::logger('system'), $e);
    }
  }

  if ($sandbox['max'] > 0 && $end < $sandbox['max']) {
    $sandbox['progress'] = $end;
    $sandbox['#finished'] = ($end - 1) / $sandbox['max'];
  }
  else {
    $sandbox['#finished'] = 1;
  }
}
