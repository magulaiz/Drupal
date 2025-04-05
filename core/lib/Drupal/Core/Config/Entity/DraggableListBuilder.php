<?php

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\DraggableListBuilderTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a base class for draggable list builders of configuration entities.
 *
 * This class enables drag-and-drop reordering of configuration entities in
 * the admin UI. It requires that the entity type defines a "weight" entity key,
 * which maps to a field that stores the position/order of the entity.
 *
 * Extend this class when you want to provide a sortable list of config entities,
 * such as vocabularies, menus, or any other list where order matters.
 *
 * Requirements:
 * - The entity type must define a 'weight' key in its entity_keys.
 * - The weight field must exist and be accessible (get/set).
 *
 * @see \Drupal\Core\Entity\DraggableListBuilderTrait
 * @see \Drupal\Core\Config\Entity\ConfigEntityListBuilder
 */
abstract class DraggableListBuilder extends ConfigEntityListBuilder implements FormInterface {

  use DraggableListBuilderTrait;

  /**
   * Constructs a new DraggableListBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The storage handler for the entity type.
   *
   * This constructor sets up the form builder for compatibility, disables
   * pagination (to allow full drag-and-drop), and stores the weight key
   * if it is defined in the entity type.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);

    // Do not inject the form builder for backwards-compatibility.
    $this->formBuilder = \Drupal::formBuilder();

    // Check if the entity type supports weighting and store the key.
    if ($this->entityType->hasKey('weight')) {
      $this->weightKey = $this->entityType->getKey('weight');
    }

    // Disable limit to load all entities for full drag-and-drop support.
    $this->limit = FALSE;
  }

  /**
   * Gets the weight of a config entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The configuration entity whose weight should be returned.
   *
   * @return int|float
   *   The weight value. Defaults to 0 if not set.
   */
  protected function getWeight(EntityInterface $entity): int|float {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    return $entity->get($this->weightKey) ?: 0;
  }

  /**
   * Sets the weight of a config entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The configuration entity whose weight should be updated.
   * @param int|float $weight
   *   The new weight value to assign.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The updated entity object.
   */
  protected function setWeight(EntityInterface $entity, int|float $weight): EntityInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $entity->set($this->weightKey, $weight);
    return $entity;
  }

}
