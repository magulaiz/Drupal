<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to set a callable used to provide the entity URI.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class UriCallback extends EntityTypeProperty {

  /**
   * Constructs a UriCallback attribute.
   *
   * @param callable $callback
   *   A callback to use to provide a URI for the entity.
   */
  public function __construct($callback) {
    parent::__construct('uri_callback', $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }
    return $definition->setUriCallback($this->value);
  }

}
