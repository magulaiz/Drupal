<?php

namespace Drupal\views;

/**
 * A class representing a view result row.
 */
class ResultRow {

  /**
   * Raw row data.
   */
  protected \stdClass $data;

  /**
   * The entity for this result.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  // phpcs:ignore Drupal.Classes.PropertyDeclaration
  public $_entity = NULL;

  /**
   * An array of relationship entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  // phpcs:ignore Drupal.Classes.PropertyDeclaration
  public $_relationship_entities = [];

  /**
   * An incremental number which represents the row in the entire result.
   *
   * @var int
   */
  public $index;

  /**
   * Constructs a ResultRow object.
   *
   * @param array $values
   *   (optional) An array of values to add as properties on the object.
   */
  public function __construct(array $values = []) {
    if (!isset($values['data'])) {
      $this->data = new \stdClass();
    }
    else {
      $this->data = $values['data'];
      unset($values['data']);
    }
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

  /**
   * Returns the raw row data.
   */
  public function getData(): \stdClass {
    return $this->data;
  }

  /**
   * Resets the _entity and _relationship_entities properties.
   */
  public function resetEntityData() {
    $this->_entity = NULL;
    $this->_relationship_entities = [];
  }

  /**
   * Implements the magic method for getting object properties.
   *
   * @param string $name
   *   Property name.
   *
   * @return mixed
   *   The value of the property.
   */
  public function __get(string $name): mixed {
    if (property_exists($this->data, $name)) {
      return $this->data->$name;
    }
    throw new \UnexpectedValueException("Property {$name} does not exist");
  }

  /**
   * Implements the magic method to determine whether a property is set.
   *
   * @param string $name
   *   Property name.
   *
   * @return bool
   *   True if property is set.
   */
  public function __isset(string $name): bool {
    return isset($this->data->$name);
  }

  /**
   * Implements the magic method to set a property.
   *
   * @param $name
   *   Property name.
   * @param mixed $value
   *   The value of the property.
   */
  public function __set(string $name, mixed $value): void {
    $this->data->$name = $value;
  }

  /**
   * Implements the magic method to unset a property.
   *
   * @param $name
   *   Property name.
   */
  public function __unset($name): void {
    unset($this->data->$name);
  }

}
