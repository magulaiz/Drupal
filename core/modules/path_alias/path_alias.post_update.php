<?php

/**
 * @file
 * Post update functions for Path Alias.
 */

/**
 * Remove the path_alias__status index.
 */
function path_alias_post_update_drop_path_alias_status_index(): void {
  /** @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager */
  $update_manager = \Drupal::service('entity.definition_update_manager');
  $entity_type = $update_manager->getEntityType('path_alias');
  $update_manager->updateEntityType($entity_type);
}

/**
 * Re-save all the path alias to removing trailing space and slash.
 */
function path_alias_post_update_removing_trailing_space_slash(&$sandbox) {
  $path_aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadMultiple();

  // Use the sandbox to store the information needed to track progression.
  if (!isset($sandbox['current'])) {
    // The count of entities visited so far.
    $sandbox['current'] = 0;
    // Total entities that must be visited.
    $sandbox['max'] = count($path_aliases);
  }
  $path_aliases = array_slice($path_aliases, (int) $sandbox['current'], 50);

  /** @var \Drupal\path_alias\Entity\PathAlias $alias */
  foreach ($path_aliases as $alias) {
    $alias->save();
    $sandbox['current']++;
  }
  $sandbox['#finished'] = empty($sandbox['max']) ? 1 : ($sandbox['current'] / $sandbox['max']);

  if ($sandbox['#finished'] >= 1) {
    return t('The batch Path Alias to remove trailing space and slash is completed.');
  }
}
