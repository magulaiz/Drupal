<?php

namespace Drupal\jsonapi\Events;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\Component\EventDispatcher\Event;

/**
 * An event used for collecting resource object metadata of a JSON:API resource types.
 */
final class CollectResourceObjectMetaEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The resource object.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject
   */
  private $resourceObject;

  /**
   * The context options from the normalizer.
   *
   * @var array
   */
  private $context = [];

  /**
   * The metadata.
   *
   * @var array
   */
  private $meta = [];

  /**
   * Constructs a new CollectResourceObjectMetaEvent object.
   *
   * @param \Drupal\jsonapi\JsonApiResource\ResourceObject $resource_object
   *   The resource object.
   * @param array $context
   *   The context options for the normalizer.
   */
  public function __construct(ResourceObject $resource_object, array $context) {
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
   *   The meta
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
  public function setMeta($property, $value): CollectResourceObjectMetaEvent {
    NestedArray::setValue($this->meta, (array) $property, $value, TRUE);
    return $this;
  }

}
