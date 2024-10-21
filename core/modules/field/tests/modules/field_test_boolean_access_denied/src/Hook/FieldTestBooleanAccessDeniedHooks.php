<?php

namespace Drupal\field_test_boolean_access_denied\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class FieldTestBooleanAccessDeniedHooks
{
    /**
     * Implements hook_entity_field_access().
     */
    #[Hook('entity_field_access')]
    public function entityFieldAccess($operation, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, \Drupal\Core\Session\AccountInterface $account, ?\Drupal\Core\Field\FieldItemListInterface $items = \NULL)
    {
        return \Drupal\Core\Access\AccessResult::forbiddenIf($field_definition->getName() === \Drupal::state()->get('field.test_boolean_field_access_field'));
    }
}
