<?php

declare(strict_types=1);

namespace Drupal\workspaces_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class WorkspacesTestHooks {

  /**
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) {
    $state = \Drupal::state();
    // Allow all entity types to have their definition changed dynamically for
    // testing purposes.
    foreach ($entity_types as $entity_type_id => $entity_type) {
      $entity_types[$entity_type_id] = $state->get("{$entity_type_id}.entity_type", $entity_types[$entity_type_id]);
    }
  }

}
