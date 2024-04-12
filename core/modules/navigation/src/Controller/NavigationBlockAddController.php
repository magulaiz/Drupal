<?php

namespace Drupal\navigation\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for building the navigation block instance add form.
 */
class NavigationBlockAddController extends ControllerBase {

  /**
   * Build the navigation block instance add form.
   *
   * @param string $plugin_id
   *   The plugin ID for the navigation block instance.
   *
   * @return array
   *   The navigation block instance edit form.
   */
  public function navigationBlockAddConfigureForm($plugin_id) {
    // Create a navigation block entity.
    $entity = $this->entityTypeManager()->getStorage('navigation_block')->create(['plugin' => $plugin_id]);

    return $this->entityFormBuilder()->getForm($entity);
  }

}
