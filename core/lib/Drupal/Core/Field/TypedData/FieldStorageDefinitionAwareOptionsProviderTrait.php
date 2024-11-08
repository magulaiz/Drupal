<?php

namespace Drupal\Core\Field\TypedData;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Trait implementing FieldStorageDefinitionAwareOptionsProviderInterface.
 */
trait FieldStorageDefinitionAwareOptionsProviderTrait {

  /**
   * The field storage definition.
   *
   * @var \Drupal\Core\Field\FieldStorageDefinitionInterface
   */
  protected $fieldStorageDefinition;

  /**
   * Sets the field storage definition.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   The field definition where the options provider is defined.
   *
   * @return $this
   */
  public function setFieldStorageDefinition(FieldStorageDefinitionInterface $definition) {
    $this->fieldStorageDefinition = $definition;
    return $this;
  }

  /**
   * Gets the field storage definition.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface
   *   The field storage definition.
   *
   * @throws \LogicException
   *   Thrown when the field storage definition is not available.
   */
  public function getFieldStorageDefinition() {
    if (!$this->fieldStorageDefinition) {
      throw new \LogicException("No field storage definition available. This options provider must be used on field definitions.");
    }
    return $this->fieldStorageDefinition;
  }

}
