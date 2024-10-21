<?php

namespace Drupal\workflow_type_test\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflow_type_test\Plugin\WorkflowType\WorkflowCustomAccessType;
use Drupal\workflows\WorkflowInterface;
use Drupal\Core\Hook\Attribute\Hook;
class WorkflowTypeTestHooks
{
    /**
     * Implements hook_workflow_type_info_alter().
     */
    #[Hook('workflow_type_info_alter')]
    public function workflowTypeInfoAlter(&$definitions)
    {
        // Allow tests to override the workflow type definitions.
        $state = \Drupal::state();
        if ($state->get('workflow_type_test.plugin_definitions') !== \NULL) {
            $definitions = $state->get('workflow_type_test.plugin_definitions');
        }
    }
    /**
     * Implements hook_ENTITY_TYPE_access() for the Workflow entity type.
     */
    #[Hook('workflow_access')]
    public function workflowAccess(\Drupal\workflows\WorkflowInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        if ($entity->getTypePlugin()->getPluginId() === 'workflow_custom_access_type') {
            return \Drupal\workflow_type_test\Plugin\WorkflowType\WorkflowCustomAccessType::workflowAccess($entity, $operation, $account);
        }
        return \Drupal\Core\Access\AccessResult::neutral();
    }
}
