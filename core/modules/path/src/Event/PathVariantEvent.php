<?php

declare(strict_types=1);

namespace Drupal\path\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\path\PathVariant\PathVariant;

/**
 * Event for collecting path variants for an entity/bundle combination.
 *
 * @phpstan-type Variants iterable<\Drupal\path\PathVariant\PathVariant>
 */
final class PathVariantEvent {

  /**
   * @var list<\Drupal\path\PathVariant\PathVariant>
   */
  private array $variants = [];

  /**
   * Constructs a new PathVariantEvent.
   */
  private function __construct(
    private string $entityTypeId,
    private string $bundle,
  ) {
  }

  /**
   * Factory for creating a new path variant event.
   *
   * @internal
   *   Not for public use.
   */
  public static function create(
    string $entityTypeId,
    string $bundle,
  ): static {
    return new static($entityTypeId, $bundle);
  }

  /**
   * Factory for creating a new path variant event from an entity.
   *
   * @internal
   *    Not for public use.
   */
  public static function createFromEntity(EntityInterface $entity): static {
    return new static($entity->getEntityTypeId(), $entity->bundle());
  }

  /**
   * Get the entity type ID.
   *
   * @phpstan-pure
   */
  public function getEntityTypeId(): string {
    return $this->entityTypeId;
  }

  /**
   * Get the bundle.
   *
   * @phpstan-pure
   */
  public function getBundle(): string {
    return $this->bundle;
  }

  /**
   * Adds a variant.
   *
   * @return $this
   *   The object itself for chaining.
   */
  public function addVariant(PathVariant $variant): static {
    $this->variants[] = $variant;

    return $this;
  }

  /**
   * Get the variants for this entity type and bundle.
   *
   * @return Variants
   *
   * @internal
   *    Not for public use.
   */
  public function getVariants(): iterable {
    return $this->variants;
  }

}
