<?php

namespace Drupal\Tests\Core\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\RequiredFieldStorageDefinitionInterface;

/**
 * Defines a test interface to mock entity base field definitions.
 */
interface TestBaseFieldDefinitionInterface extends FieldDefinitionInterface, FieldStorageDefinitionInterface, RequiredFieldStorageDefinitionInterface {
}
