<?php

declare(strict_types=1);

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add storage handler property to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class StorageClass extends EntityTypeProperty {

  /**
   * Constructs a StorageClass attribute.
   *
   * @param class-string $storageClass
   *   The class for the entity type's storage.
   */
  public function __construct(public readonly string $storageClass) {
    parent::__construct(['handlers', 'storage'], $storageClass);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }
    return $definition->setStorageClass($this->storageClass);
  }

}
