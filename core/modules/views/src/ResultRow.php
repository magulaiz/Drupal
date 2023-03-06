<?php

namespace Drupal\views;

/**
 * A class representing a view result row.
 */
class ResultRow {

  /**
   * Raw row data.
   */
  protected array $data;

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
      $this->data = [];
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
   * Checks if a named column is in the result row.
   *
   * @param string $name
   *   The name of the column to check existence of.
   *
   * @return bool
   *   TRUE if the result row contains a column of the given name.
   */
  public function hasColumn(string $name): bool {
    return array_key_exists($name, $this->data);
  }

  /**
   * Resets the _entity and _relationship_entities properties.
   */
  public function resetEntityData() {
    $this->_entity = NULL;
    $this->_relationship_entities = [];
  }

  /**
   * {@inheritdoc}
   */
  public function __get(string $name): mixed {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    throw new \UnexpectedValueException("Column '{$name}' does not exist");
  }

  /**
   * {@inheritdoc}
   */
  public function __isset(string $name): bool {
    return isset($this->data[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function __set(string $name, mixed $value): void {
    $this->data[$name] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function __unset($name): void {
    unset($this->data[$name]);
  }

}
