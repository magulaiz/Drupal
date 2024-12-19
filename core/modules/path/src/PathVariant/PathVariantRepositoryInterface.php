<?php

declare(strict_types=1);

namespace Drupal\path\PathVariant;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for path variant repository.
 *
 * @phpstan-import-type InternalPaths from \Drupal\path\Event\EntityPathsEvent
 * @phpstan-import-type Variants from \Drupal\path\Event\PathVariantEvent
 */
interface PathVariantRepositoryInterface {

  /**
   * Get internal paths for an entity.
   *
   * When final internal paths cannot be computed, such as before an entity is
   * saved for the first time, a placeholder value object is used.
   *
   * @return InternalPaths
   */
  public function getInternalPaths(EntityInterface $entity): iterable;

  /**
   * Get an internal path for the path variant of an entity.
   *
   * @return non-empty-string|\Drupal\path\PathVariant\PlaceHolderInternalPath
   *   The internal path.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the path variant does not exist.
   */
  public function getInternalPathByPathVariant(EntityInterface $entity, PathVariant $pathVariant): string|PlaceHolderInternalPath;

  /**
   * Get the default path variant.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the path variant does not exist.
   */
  public function getDefaultPathVariant(EntityInterface $entity): PathVariant;

  /**
   * Get the path variants for an entity.
   *
   * @return Variants
   */
  public function getPathVariantsForEntity(EntityInterface $entity): iterable;

}
