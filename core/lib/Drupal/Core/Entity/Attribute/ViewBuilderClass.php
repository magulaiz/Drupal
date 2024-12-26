<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add view builder class to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ViewBuilderClass extends EntityTypeProperty {

  /**
   * Constructs a ViewBuilderClass attribute.
   *
   * @param class-string $viewBuilderClass
   *   The class for this entity type's view builder.
   */
  public function __construct(public readonly string $viewBuilderClass) {
    parent::__construct(['handlers', 'view_builder'], $this->viewBuilderClass);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }
    return $definition->setViewBuilderClass($this->viewBuilderClass);
  }

}
