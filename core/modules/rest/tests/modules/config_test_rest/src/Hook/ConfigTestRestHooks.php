<?php

namespace Drupal\config_test_rest\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class ConfigTestRestHooks
{
    /**
     * Implements hook_entity_type_alter().
     */
    #[Hook('entity_type_alter')]
    public function entityTypeAlter(array &$entity_types)
    {
        // Undo part of what config_test_entity_type_alter() did: remove this
        // config_test_no_status entity type, because it uses the same entity class as
        // the config_test entity type, which makes REST deserialization impossible.
        unset($entity_types['config_test_no_status']);
    }
    /**
     * Implements hook_ENTITY_TYPE_access().
     */
    #[Hook('config_test_access')]
    public function configTestAccess(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        // Add permission, so that EntityResourceTestBase's scenarios can test access
        // being denied. By default, all access is always allowed for the config_test
        // config entity.
        $access_result = \Drupal\Core\Access\AccessResult::forbiddenIf(!$account->hasPermission('view config_test'))->cachePerPermissions();
        if (!$access_result->isAllowed() && $access_result instanceof \Drupal\Core\Access\AccessResultReasonInterface) {
            $access_result->setReason("The 'view config_test' permission is required.");
        }
        return $access_result;
    }
}
