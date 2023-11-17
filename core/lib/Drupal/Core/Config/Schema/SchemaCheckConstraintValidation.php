<?php

namespace Drupal\Core\Config\Schema;

/**
 * Determines how SchemaCheckTrait::checkConfigSchema() behaves.
 *
 * This is an enum to allow us to add additional behaviors in the future.
 *
 * @see \Drupal\Core\Config\Schema\SchemaCheckTrait::checkConfigSchema()
 */
enum SchemaCheckConstraintValidation {

  // Do not do constraint validation.
  case NoValidation;

  // Add constraint validation errors to the list of errors.
  case Error;

  /**
   * Determines if constraints should be validated.
   *
   * @return bool
   *   TRUE if constraints should be validated, FALSE if not.
   */
  public function doValidation(): bool {
    return $this === self::Error;
  }

}
