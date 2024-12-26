<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add list builder class to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ListBuilderClass extends EntityTypeProperty {

  /**
   * Constructs a ListBuilderClass attribute.
   *
   * @param class-string $listBuilderClass
   *   The list class to use for the entity type.
   */
  public function __construct(public readonly string $listBuilderClass) {
    parent::__construct(['handlers', 'list_builder'], $this->listBuilderClass);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }
    return $definition->setListBuilderClass($this->listBuilderClass);
  }

}
