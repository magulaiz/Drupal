<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity;

interface EntityStorageByClassInterface {

  /**
   * Gets an entity storage for a given entity class.
   *
   * @template TEntity of \Drupal\Core\Entity\EntityInterface
   *
   * @param class-string<TEntity> $class
   *   Entity class or interface.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface<TEntity>
   *   Entity storage for that entity type.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   The entity type doesn't exist, or it cannot be identified just from the
   *   class or interface.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function getStorageByClass(string $class, ?string $entity_type_id = NULL): EntityStorageInterface;

}
