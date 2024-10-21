<?php

/**
 * @file
 * Defines a field type and its formatters and widgets.
 */

declare(strict_types=1);

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Sample 'default value' callback.
 */
function field_test_default_value(FieldableEntityInterface $entity, FieldDefinitionInterface $definition) {
  return [['value' => 99]];
}
