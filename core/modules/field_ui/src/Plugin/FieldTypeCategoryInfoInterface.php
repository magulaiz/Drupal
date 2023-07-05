<?php

namespace Drupal\field_ui\Plugin;

/**
 * Provides an object that returns the category info about the field type.
 */
interface FieldTypeCategoryInfoInterface {

  /**
   * Returns the field group label.
   */
  public function getLabel();

  /**
   * Returns the field group description.
   */
  public function getDescription();

  /**
   * Returns the field group weight.
   */
  public function getWeight();

}
