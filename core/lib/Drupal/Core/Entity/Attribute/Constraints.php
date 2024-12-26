<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add constraints to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Constraints extends EntityTypePropertyBase {

  /**
   * Constructs a Constraints attribute.
   *
   * @param array $constraints
   *   An array of validation constraint definitions, keyed by constraint name.
   *   Each constraint definition can be used for instantiating
   *   \Symfony\Component\Validator\Constraint objects.
   */
  public function __construct(public readonly array $constraints) {}

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getPluginClass()));
    }
    return $definition->setConstraints($this->constraints);
  }

}
