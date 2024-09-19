<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Field;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines a test interface to mock entity base field definitions.
 *
 * @todo This is not possible anymore. fix by converting to abstract class
 * which implements both FDI and FSDI ?
 */
interface TestBaseFieldDefinitionInterface extends FieldStorageDefinitionInterface {

}
