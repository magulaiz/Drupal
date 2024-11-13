<?php

namespace Drupal\content_moderation\Hook;

use Drupal\content_moderation\EntityOperations;
use Drupal\content_moderation\EntityTypeInfo;
use Drupal\content_moderation\ContentPreprocess;
use Drupal\content_moderation\Plugin\Action\ModerationOptOutPublish;
use Drupal\content_moderation\Plugin\Action\ModerationOptOutUnpublish;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\filter\Broken;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Drupal\workflows\WorkflowInterface;
use Drupal\Core\Action\Plugin\Action\PublishAction;
use Drupal\Core\Action\Plugin\Action\UnpublishAction;
use Drupal\workflows\Entity\Workflow;
use Drupal\views\Entity\View;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for content_moderation.
 */
class ContentModerationHooks
{
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
        switch ($route_name) {
            // Main module help for the content_moderation module.
            case 'help.page.content_moderation':
                $output = '';
                $output .= '<h2>' . t('About') . '</h2>';
                $output .= '<p>' . t('The Content Moderation module allows you to expand on Drupal\'s "unpublished" and "published" states for content. It allows you to have a published version that is live, but have a separate working copy that is undergoing review before it is published. This is achieved by using <a href=":workflows">Workflows</a> to apply different states and transitions to entities as needed. For more information, see the <a href=":content_moderation">online documentation for the Content Moderation module</a>.', [
                    ':content_moderation' => 'https://www.drupal.org/documentation/modules/content_moderation',
                    ':workflows' => \Drupal\Core\Url::fromRoute('help.page', [
                        'name' => 'workflows',
                    ])->toString(),
                ]) . '</p>';
                $output .= '<h2>' . t('Uses') . '</h2>';
                $output .= '<dl>';
                $output .= '<dt>' . t('Applying workflows') . '</dt>';
                $output .= '<dd>' . t('Content Moderation allows you to apply <a href=":workflows">Workflows</a> to content, content blocks, and other <a href=":field_help" title="Field module help, with background on content entities">content entities</a>, to provide more fine-grained publishing options. For example, a Basic page might have states such as Draft and Published, with allowed transitions such as Draft to Published (making the current revision "live"), and Published to Draft (making a new draft revision of published content).', [
                    ':workflows' => \Drupal\Core\Url::fromRoute('help.page', [
                        'name' => 'workflows',
                    ])->toString(),
                    ':field_help' => \Drupal\Core\Url::fromRoute('help.page', [
                        'name' => 'field',
                    ])->toString(),
                ]) . '</dd>';
                if (\Drupal::moduleHandler()->moduleExists('views')) {
                    $moderated_content_view = \Drupal\views\Entity\View::load('moderated_content');
                    if (isset($moderated_content_view) && $moderated_content_view->status() === TRUE) {
                        $output .= '<dt>' . t('Moderating content') . '</dt>';
                        $output .= '<dd>' . t('You can view a list of content awaiting moderation on the <a href=":moderated">moderated content page</a>. This will show any content in an unpublished state, such as Draft or Archived, to help surface content that requires more work from content editors.', [
                            ':moderated' => \Drupal\Core\Url::fromRoute('view.moderated_content.moderated_content')->toString(),
                        ]) . '</dd>';
                    }
                }
                $output .= '<dt>' . t('Configure Content Moderation permissions') . '</dt>';
                $output .= '<dd>' . t('Each transition is exposed as a permission. If a user has the permission for a transition, they can use the transition to change the state of the content item, from Draft to Published.') . '</dd>';
                $output .= '</dl>';
                return $output;
        }
    }
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityTypeInfo::class)->entityBaseFieldInfo($entity_type);
    }
    /**
     * Implements hook_entity_bundle_field_info().
     */
    #[Hook('entity_bundle_field_info')]
    public function entityBundleFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type, $bundle, array $base_field_definitions)
    {
        if (isset($base_field_definitions['moderation_state'])) {
            // Add the target bundle to the moderation state field. Since each bundle
            // can be attached to a different moderation workflow, adding this
            // information to the field definition allows the associated workflow to be
            // derived where a field definition is present.
            $base_field_definitions['moderation_state']->setTargetBundle($bundle);
            return ['moderation_state' => $base_field_definitions['moderation_state']];
        }
    }
    /**
     * Implements hook_entity_type_alter().
     */
    #[Hook('entity_type_alter')]
    public function entityTypeAlter(array &$entity_types)
    {
        \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityTypeInfo::class)->entityTypeAlter($entity_types);
    }
    /**
     * Implements hook_entity_presave().
     */
    #[Hook('entity_presave')]
    public function entityPresave(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityPresave($entity);
    }
    /**
     * Implements hook_entity_insert().
     */
    #[Hook('entity_insert')]
    public function entityInsert(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityInsert($entity);
    }
    /**
     * Implements hook_entity_update().
     */
    #[Hook('entity_update')]
    public function entityUpdate(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityUpdate($entity);
    }
    /**
     * Implements hook_entity_delete().
     */
    #[Hook('entity_delete')]
    public function entityDelete(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityDelete($entity);
    }
    /**
     * Implements hook_entity_revision_delete().
     */
    #[Hook('entity_revision_delete')]
    public function entityRevisionDelete(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityRevisionDelete($entity);
    }
    /**
     * Implements hook_entity_translation_delete().
     */
    #[Hook('entity_translation_delete')]
    public function entityTranslationDelete(\Drupal\Core\Entity\EntityInterface $translation)
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityTranslationDelete($translation);
    }
    /**
     * Implements hook_entity_prepare_form().
     */
    #[Hook('entity_prepare_form')]
    public function entityPrepareForm(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityTypeInfo::class)->entityPrepareForm($entity, $operation, $form_state);
    }
    /**
     * Implements hook_form_alter().
     */
    #[Hook('form_alter')]
    public function formAlter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) : void
    {
        \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityTypeInfo::class)->formAlter($form, $form_state, $form_id);
    }
    /**
     * Implements hook_entity_extra_field_info().
     */
    #[Hook('entity_extra_field_info')]
    public function entityExtraFieldInfo()
    {
        return \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityTypeInfo::class)->entityExtraFieldInfo();
    }
    /**
     * Implements hook_entity_view().
     */
    #[Hook('entity_view')]
    public function entityView(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode)
    {
        \Drupal::service('class_resolver')->getInstanceFromDefinition(\Drupal\content_moderation\EntityOperations::class)->entityView($build, $entity, $display, $view_mode);
    }
    /**
     * Implements hook_entity_form_display_alter().
     */
    #[Hook('entity_form_display_alter')]
    public function entityFormDisplayAlter(\Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display, array $context)
    {
        if ($context['form_mode'] === 'layout_builder') {
            $form_display->setComponent('moderation_state', ['type' => 'moderation_state_default', 'weight' => -900, 'settings' => []]);
        }
    }
    /**
     * Implements hook_entity_access().
     *
     * Entities should be viewable if unpublished and the user has the appropriate
     * permission. This permission is therefore effectively mandatory for any user
     * that wants to moderate things.
     */
    #[Hook('entity_access')]
    public function entityAccess(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
        $moderation_info = \Drupal::service('content_moderation.moderation_information');
        $access_result = NULL;
        if ($operation === 'view') {
            $access_result = $entity instanceof \Drupal\Core\Entity\EntityPublishedInterface && !$entity->isPublished() ? \Drupal\Core\Access\AccessResult::allowedIfHasPermission($account, 'view any unpublished content') : \Drupal\Core\Access\AccessResult::neutral();
            $access_result->addCacheableDependency($entity);
        } elseif ($operation === 'update' && $moderation_info->isModeratedEntity($entity) && $entity->moderation_state) {
            /** @var \Drupal\content_moderation\StateTransitionValidation $transition_validation */
            $transition_validation = \Drupal::service('content_moderation.state_transition_validation');
            $valid_transition_targets = $transition_validation->getValidTransitions($entity, $account);
            $access_result = $valid_transition_targets ? \Drupal\Core\Access\AccessResult::neutral() : \Drupal\Core\Access\AccessResult::forbidden('No valid transitions exist for given account.');
            $access_result->addCacheableDependency($entity);
            $workflow = $moderation_info->getWorkflowForEntity($entity);
            $access_result->addCacheableDependency($workflow);
            // The state transition validation service returns a list of transitions
            // based on the user's permission to use them.
            $access_result->cachePerPermissions();
        }
        // Do not allow users to delete the state that is configured as the default
        // state for the workflow.
        if ($entity instanceof \Drupal\workflows\WorkflowInterface) {
            $configuration = $entity->getTypePlugin()->getConfiguration();
            if (!empty($configuration['default_moderation_state']) && $operation === sprintf('delete-state:%s', $configuration['default_moderation_state'])) {
                return \Drupal\Core\Access\AccessResult::forbidden()->addCacheableDependency($entity);
            }
        }
        return $access_result;
    }
    /**
     * Implements hook_entity_field_access().
     */
    #[Hook('entity_field_access')]
    public function entityFieldAccess($operation, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, \Drupal\Core\Session\AccountInterface $account, ?\Drupal\Core\Field\FieldItemListInterface $items = NULL)
    {
        if ($items && $operation === 'edit') {
            /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
            $moderation_info = \Drupal::service('content_moderation.moderation_information');
            $entity_type = \Drupal::entityTypeManager()->getDefinition($field_definition->getTargetEntityTypeId());
            $entity = $items->getEntity();
            // Deny edit access to the published field if the entity is being moderated.
            if ($entity_type->hasKey('published') && $moderation_info->isModeratedEntity($entity) && $entity->moderation_state && $field_definition->getName() == $entity_type->getKey('published')) {
                return \Drupal\Core\Access\AccessResult::forbidden('Cannot edit the published field of moderated entities.');
            }
        }
        return \Drupal\Core\Access\AccessResult::neutral();
    }
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme() : array
    {
        return ['entity_moderation_form' => ['render element' => 'form']];
    }
    /**
     * Implements hook_action_info_alter().
     */
    #[Hook('action_info_alter')]
    public function actionInfoAlter(&$definitions)
    {
        // The publish/unpublish actions are not valid on moderated entities. So swap
        // their implementations out for alternates that will become a no-op on a
        // moderated entity. If another module has already swapped out those classes,
        // though, we'll be polite and do nothing.
        foreach ($definitions as &$definition) {
            if ($definition['id'] === 'entity:publish_action' && $definition['class'] == \Drupal\Core\Action\Plugin\Action\PublishAction::class) {
                $definition['class'] = \Drupal\content_moderation\Plugin\Action\ModerationOptOutPublish::class;
            }
            if ($definition['id'] === 'entity:unpublish_action' && $definition['class'] == \Drupal\Core\Action\Plugin\Action\UnpublishAction::class) {
                $definition['class'] = \Drupal\content_moderation\Plugin\Action\ModerationOptOutUnpublish::class;
            }
        }
    }
    /**
     * Implements hook_entity_bundle_info_alter().
     */
    #[Hook('entity_bundle_info_alter')]
    public function entityBundleInfoAlter(&$bundles)
    {
        $translatable = FALSE;
        /** @var \Drupal\workflows\WorkflowInterface $workflow */
        foreach (\Drupal\workflows\Entity\Workflow::loadMultipleByType('content_moderation') as $workflow) {
            /** @var \Drupal\content_moderation\Plugin\WorkflowType\ContentModeration $plugin */
            $plugin = $workflow->getTypePlugin();
            foreach ($plugin->getEntityTypes() as $entity_type_id) {
                foreach ($plugin->getBundlesForEntityType($entity_type_id) as $bundle_id) {
                    if (isset($bundles[$entity_type_id][$bundle_id])) {
                        $bundles[$entity_type_id][$bundle_id]['workflow'] = $workflow->id();
                        // If we have even one moderation-enabled translatable bundle, we need
                        // to make the moderation state bundle translatable as well, to enable
                        // the revision translation merge logic also for content moderation
                        // state revisions.
                        if (!empty($bundles[$entity_type_id][$bundle_id]['translatable'])) {
                            $translatable = TRUE;
                        }
                    }
                }
            }
        }
        $bundles['content_moderation_state']['content_moderation_state']['translatable'] = $translatable;
    }
    /**
     * Implements hook_entity_bundle_delete().
     */
    #[Hook('entity_bundle_delete')]
    public function entityBundleDelete($entity_type_id, $bundle_id)
    {
        // Remove non-configuration based bundles from content moderation based
        // workflows when they are removed.
        foreach (\Drupal\workflows\Entity\Workflow::loadMultipleByType('content_moderation') as $workflow) {
            if ($workflow->getTypePlugin()->appliesToEntityTypeAndBundle($entity_type_id, $bundle_id)) {
                $workflow->getTypePlugin()->removeEntityTypeAndBundle($entity_type_id, $bundle_id);
                $workflow->save();
            }
        }
    }
    /**
     * Implements hook_ENTITY_TYPE_insert().
     */
    #[Hook('workflow_insert')]
    public function workflowInsert(\Drupal\workflows\WorkflowInterface $entity)
    {
        // Clear bundle cache so workflow gets added or removed from the bundle
        // information.
        \Drupal::service('entity_type.bundle.info')->clearCachedBundles();
        // Clear field cache so extra field is added or removed.
        \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
        // Clear the views data cache so the extra field is available in views.
        if (\Drupal::moduleHandler()->moduleExists('views')) {
            \Drupal\views\Views::viewsData()->clear();
        }
    }
    /**
     * Implements hook_ENTITY_TYPE_update().
     */
    #[Hook('workflow_update')]
    public function workflowUpdate(\Drupal\workflows\WorkflowInterface $entity)
    {
        // Clear bundle cache so workflow gets added or removed from the bundle
        // information.
        \Drupal::service('entity_type.bundle.info')->clearCachedBundles();
        // Clear field cache so extra field is added or removed.
        \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
        // Clear the views data cache so the extra field is available in views.
        if (\Drupal::moduleHandler()->moduleExists('views')) {
            \Drupal\views\Views::viewsData()->clear();
        }
    }
    /**
     * Implements hook_views_post_execute().
     */
    #[Hook('views_post_execute')]
    public function viewsPostExecute(\Drupal\views\ViewExecutable $view)
    {
        // @todo Remove this once broken handlers in views configuration result in
        //   a view no longer returning results. https://www.drupal.org/node/2907954.
        foreach ($view->filter as $id => $filter) {
            if (str_starts_with($id, 'moderation_state') && $filter instanceof \Drupal\views\Plugin\views\filter\Broken) {
                $view->result = [];
                break;
            }
        }
    }
}
