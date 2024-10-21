<?php

namespace Drupal\image_access_test_hidden\Hook;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Hook\Attribute\Hook;
class ImageAccessTestHiddenHooks
{
    /**
     * Implements hook_entity_field_access().
     */
    #[Hook('entity_field_access')]
    public function entityFieldAccess($operation, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, \Drupal\Core\Session\AccountInterface $account, ?\Drupal\Core\Field\FieldItemListInterface $items = \NULL)
    {
        if ($field_definition->getName() == 'field_image' && $operation == 'edit') {
            return \Drupal\Core\Access\AccessResult::forbidden();
        }
        return \Drupal\Core\Access\AccessResult::neutral();
    }
}
