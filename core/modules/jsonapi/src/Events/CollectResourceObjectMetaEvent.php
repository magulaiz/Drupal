<?php

declare(strict_types = 1);

namespace Drupal\jsonapi\Events;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\Component\EventDispatcher\Event;

/**
 * An event used for collecting resource object metadata of a JSON:API resource types.
 *
 * When subscribing to this event you should make sure you set the correct cache tags and contexts. These will then
 * bubble up to the normalization.
 *
 * This class does not automatically add the cache tags and contexts. You can do anything in the meta event subscriber,
 * but you should make sure you add the correct cache tags and contexts to the normalization based on the data you add
 * to the metadata.
 */
final class CollectResourceObjectMetaEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The resource object.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject
   */
  private ResourceObject $resourceObject;

  /**
   * The context options from the normalizer.
   *
   * @var array
   */
  private array $context = [];

  /**
   * The metadata.
   *
   * @var array
   */
  private array $meta = [];

  /**
   * Constructs a new CollectResourceObjectMetaEvent object.
   *
   * @param \Drupal\jsonapi\JsonApiResource\ResourceObject $resource_object
   *   The resource object.
   * @param array $context
   *   The context options for the normalizer.
   */
  public function __construct(ResourceObject $resource_object, array $context) {
    assert(!empty($context['resource_object']) && $context['resource_object'] instanceof ResourceObject);

    $this->resourceObject = $resource_object;
    $this->context = $context;
  }

  /**
   * Gets the resource object.
   *
   * @return \Drupal\jsonapi\JsonApiResource\ResourceObject
   *   The resource object.
   */
  public function getResourceObject(): ResourceObject {
    return $this->resourceObject;
  }

  /**
   * Gets context options for the normalizer.
   *
   * @return array
   *   The context options for the normalizer.
   */
  public function getContext(): array {
    return $this->context;
  }

  /**
   * Gets the meta values.
   *
   * @return array
   *   The meta data.
   */
  public function getMeta(): array {
    return $this->meta;
  }

  /**
   * Sets a value value.
   *
   * @param $property
   *   The key.
   * @param $value
   *   The value.
   *
   * @return $this
   */
  public function setMeta($property, $value): self {
    NestedArray::setValue($this->meta, (array) $property, $value, TRUE);
    return $this;
  }

}
