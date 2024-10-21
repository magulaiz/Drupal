<?php

declare(strict_types=1);

namespace Drupal\node_test_views\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class NodeTestViewsViewsHooks {

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
    public function viewsDataAlter(array &$data) {
    // Make node language use the basic field handler if requested.
    if (\Drupal::state()->get('node_test_views.use_basic_handler')) {
      $data['node_field_data']['langcode']['field']['id'] = 'language';
    }
    }

}
