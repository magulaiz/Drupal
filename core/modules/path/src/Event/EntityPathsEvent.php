<?php

declare(strict_types=1);

namespace Drupal\path\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\path\PathVariant\PathVariant;
use Drupal\path\PathVariant\PlaceHolderInternalPath;

/**
 * Event for getting internal paths for an entity.
 *
 * @phpstan-type InternalPaths iterable<array{non-empty-string|\Drupal\path\PathVariant\PlaceHolderInternalPath, \Drupal\path\PathVariant\PathVariant}>
 */
final class EntityPathsEvent {

  /**
   * @var list<array{non-empty-string, \Drupal\path\PathVariant\PathVariant}>
   */
  private array $internalPaths = [];

  /**
   * Constructs a new EntityPathsEvent.
   */
  private function __construct(
    private EntityInterface $entity,
  ) {
  }

  /**
   * Factory for creating a new entity paths event.
   *
   * @internal
   *   Not for public use.
   */
  public static function create(
    EntityInterface $entity,
  ): static {
    return new static($entity,
    );
  }

  /**
   * Get the entity.
   *
   * @phpstan-pure
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * Adds an internal path.
   *
   * @param non-empty-string|\Drupal\path\PathVariant\PlaceHolderInternalPath $path
   *   A path. Must be prefixed by '/', or PlaceHolderInternalPath if the
   *   internal path cannot be determined yet.
   * @param \Drupal\path\PathVariant\PathVariant $variant
   *   The variant.
   *
   * @return $this
   *   The object itself for chaining.
   *
   * @throws \InvalidArgumentException
   *   When path is invalid.
   */
  public function addInternalPath(PlaceHolderInternalPath|string $path, PathVariant $variant): static {
    if (\is_string($path) && FALSE === \str_starts_with($path, '/')) {
      throw new \InvalidArgumentException('Path must be prefixed with forward slash.');
    }

    $this->internalPaths[] = [$path, $variant];

    return $this;
  }

  /**
   * Get the internal path and variants for this entity.
   *
   * @return InternalPaths
   *   Internal paths and variants.
   *
   * @internal
   *    Not for public use.
   */
  public function getInternalPaths(): iterable {
    return $this->internalPaths;
  }

}
