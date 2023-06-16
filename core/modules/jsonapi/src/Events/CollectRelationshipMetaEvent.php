<?php

declare(strict_types = 1);

namespace Drupal\jsonapi\Events;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\Component\EventDispatcher\Event;

/**
 * An event used for collecting resource object metadata of a JSON:API resource type relation.
 */
final class CollectRelationshipMetaEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The resource object.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject
   */
  private ResourceObject $resourceObject;

  /**
   * The relationship field's public name.
   *
   * @var string
   */
  private string $relationshipFieldName;

  /**
   * The metadata.
   *
   * @var array
   */
  private array $meta = [];

  /**
   * Constructs a new CollectRelationshipMetaEvent object.
   *
   * @param \Drupal\jsonapi\JsonApiResource\ResourceObject $resource_object
   *   The resource object.
   * @param string $relationship_field_name
   *   The relationship field.
   */
  public function __construct(ResourceObject $resource_object, string $relationship_field_name) {
    $this->resourceObject = $resource_object;
    $this->relationshipFieldName = $relationship_field_name;
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
   * Gets the relationship field.
   *
   * @return string
   *   The relationship field name.
   */
  public function getRelationshipFieldName(): string {
    return $this->relationshipFieldName;
  }

  /**
   * Gets the meta values.
   *
   * @return array
   *   The meta.
   */
  public function getMeta(): array {
    return $this->meta;
  }

  /**
   * Sets a meta value.
   *
   * @param $property
   *   The key.
   * @param $value
   *   The value.
   *
   * @return $this
   */
  public function setMetaValue($property, $value): self {
    NestedArray::setValue($this->meta, (array) $property, $value, TRUE);
    return $this;
  }

}
