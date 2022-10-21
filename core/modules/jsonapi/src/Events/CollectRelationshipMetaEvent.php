<?php

namespace Drupal\jsonapi\Events;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\Component\EventDispatcher\Event;

/**
 * An event used for collecting resource object metadata of a JSON:API resource
 * type relation.
 */
final class CollectRelationshipMetaEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The resource object.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject
   */
  private $resourceObject;

  /**
   * The relationship field.
   *
   * @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $relationshipField;

  /**
   * The metadata.
   *
   * @var array
   */
  private $meta = [];

  /**
   * Constructs a new CollectRelationshipMetaEvent object.
   *
   * @param \Drupal\jsonapi\JsonApiResource\ResourceObject $resource_object
   *   The resource object.
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $relationship_field
   *   The relationship field.
   */
  public function __construct(ResourceObject $resource_object, EntityReferenceFieldItemListInterface $relationship_field) {
    $this->resourceObject = $resource_object;
    $this->relationshipField = $relationship_field;
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
   * @return \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   *   The relationship field.
   */
  public function getRelationshipField(): EntityReferenceFieldItemListInterface {
    return $this->relationshipField;
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
