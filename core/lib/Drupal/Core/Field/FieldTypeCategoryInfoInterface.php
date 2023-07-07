<?php

namespace Drupal\Core\Field;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides an object that returns the category info about the field type.
 */
interface FieldTypeCategoryInfoInterface {

  /**
   * Returns the field group label.
   */
  public function getLabel(): TranslatableMarkup;

  /**
   * Returns the field group description.
   */
  public function getDescription(): TranslatableMarkup;

  /**
   * Returns the field group weight.
   */
  public function getWeight(): int;

}
