<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Attribute class to add a single link template to entity type definition.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class LinkTemplate extends EntityTypeProperty {

  /**
   * Constructs a LinkTemplate attribute.
   *
   * @param string $key
   *   The name of a link.
   * @param string $path
   *   The route path to use for the link.
   */
  public function __construct(
    string $key,
    public readonly string $path,
  ) {
    parent::__construct($key, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function addToDefinition(object|array $definition): EntityTypeInterface {
    if (!($definition instanceof EntityTypeInterface)) {
      throw new \InvalidArgumentException(sprintf('%s attribute can not be used with %s, because it is not an entity type definition.', static::class, $this->getClass()));
    }
    return $definition->setLinkTemplate($this->key, $this->path);
  }

}
