<?php

namespace Drupal\workspace_access_test\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class WorkspaceAccessTestHooks
{
    /**
     * Implements hook_ENTITY_TYPE_access() for the 'workspace' entity type.
     */
    #[Hook('workspace_access')]
    public function workspaceAccess(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        return \Drupal::state()->get("workspace_access_test.result.{$operation}", \Drupal\Core\Access\AccessResult::neutral());
    }
}
