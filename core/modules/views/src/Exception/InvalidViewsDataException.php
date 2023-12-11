<?php

namespace Drupal\views\Exception;

/**
 * Thrown when invalid views data is encountered.
 *
 * For example, this can occur when in provided views data the relationship
 * table points to itself or into an infinite loop or to a non existing table.
 */
class InvalidViewsDataException extends \Exception {

}
