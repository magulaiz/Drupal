<?php

/**
 * @file
 * Post update functions for Content Block.
 */

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\user\Entity\Role;
use Drupal\views\Entity\View;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_removed_post_updates().
 */
function block_content_removed_post_updates() {
  return [
    'block_content_post_update_add_views_reusable_filter' => '9.0.0',
  ];
}

/**
 * Clear the entity type cache.
 */
function block_content_post_update_entity_changed_constraint() {
  // Empty post_update hook.
}

/**
 * Moves the custom block library to Content.
 */
function block_content_post_update_move_custom_block_library() {

  if (!\Drupal::service('module_handler')->moduleExists('views')) {
    return;
  }
  if (!$view = View::load('block_content')) {
    return;
  }

  $display =& $view->getDisplay('page_1');
  if (empty($display) || $display['display_options']['path'] !== 'admin/structure/block/block-content') {
    return;
  }

  $display['display_options']['path'] = 'admin/content/block';
  $menu =& $display['display_options']['menu'];
  $menu['title'] = 'Blocks';
  $menu['description'] = 'Create and edit block content.';
  $menu['expanded'] = FALSE;
  $menu['parent'] = 'system.admin_content';
  $view->set('label', 'Content blocks');

  $view->save();
}

/**
 * Update block_content 'block library' view permission.
 */
function block_content_post_update_block_library_view_permission() {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('views.view.block_content');
  $current_perm = $config->get('display.default.display_options.access.options.perm');
  if ($current_perm === 'administer blocks') {
    $config->set('display.default.display_options.access.options.perm', 'access block library')
      ->save(TRUE);
  }
}

/**
 * Update permissions for users with "administer blocks" permission.
 */
function block_content_post_update_sort_permissions(&$sandbox = NULL) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (Role $role) {
    if ($role->hasPermission('administer blocks')) {
      $role->grantPermission('administer block content');
      $role->grantPermission('access block library');
      $role->grantPermission('administer block types');
      return TRUE;
    }
    return FALSE;
  });
}

/**
 * Add status with settings to all form displays for block_content entities.
 */
function block_content_post_update_configure_status_field_widget(&$sandbox = NULL) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'entity_form_display', function (EntityDisplayInterface $entity_form_display) {
    if ($entity_form_display->getTargetEntityTypeId() == 'block_content' && empty($entity_form_display->getComponent('status'))) {
      $entity_form_display->setComponent('status', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
      ]);
      return TRUE;
    }
    return FALSE;
  });
}

function block_content_post_update_add_status_view_updates(&$sandbox = NULL) {
  $view = \Drupal::configFactory()->getEditable('views.view.block_content');

  // Don't do anything if the view does not exist in this installation.
  if ($view->isNew()) {
    return;
  }

  // Don't do anything if the view has been modified.
  // @todo: Replace this as part of https://www.drupal.org/project/drupal/issues/3021158
  // @todo: Is this block needed?
  $hash = $view->get('_core.default_config_hash');
  $config_array = $view->getRawData();
  // Unset system added properties as hash generated without them.
  // @see \Drupal\Core\Config\ConfigInstall::createConfiguration()
  unset($config_array['uuid'], $config_array['_core']);
  if (empty($hash) || $hash != Crypt::hashBase64(serialize($config_array))) {
    return;
  }

  $published_key = \Drupal::entityDefinitionUpdateManager()->getEntityType('block_content')->getKey('published');

  // Get existing field configuration.
  $fields = $view->get("display.default.display_options.fields");

  // Find out if the status field is already present.
  $status_field_exists = in_array($published_key, array_column($fields, 'field'));

  // Add bulk form and status fields.
  $block_content_bulk_form = [
    'block_content_bulk_form' => [
      'id' => 'block_content_bulk_form',
      'table' => 'block_content',
      'field' => 'block_content_bulk_form',
      'relationship' => 'none',
      'group_type' => 'group',
      'admin_label' => '',
      'label' => '',
      'exclude' => FALSE,
      'alter' => [
        'alter_text' => FALSE,
        'text' => '',
        'make_link' => FALSE,
        'path' => '',
        'absolute' => FALSE,
        'external' => FALSE,
        'replace_spaces' => FALSE,
        'path_case' => 'none',
        'trim_whitespace' => FALSE,
        'alt' => '',
        'rel' => '',
        'link_class' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'nl2br' => FALSE,
        'max_length' => 0,
        'word_boundary' => TRUE,
        'ellipsis' => TRUE,
        'more_link' => FALSE,
        'more_link_text' => '',
        'more_link_path' => '',
        'strip_tags' => FALSE,
        'trim' => FALSE,
        'preserve_tags' => '',
        'html' => FALSE,
      ],
      'element_type' => '',
      'element_class' => '',
      'element_label_type' => '',
      'element_label_class' => '',
      'element_label_colon' => FALSE,
      'element_wrapper_type' => '',
      'element_wrapper_class' => '',
      'element_default_classes' => TRUE,
      'empty' => '',
      'hide_empty' => FALSE,
      'empty_zero' => FALSE,
      'hide_alter_empty' => TRUE,
      'action_title' => 'Action',
      'include_exclude' => 'exclude',
      'selected_actions' => [],
      'entity_type' => 'block_content',
      'plugin_id' => 'bulk_form',
    ],
  ];
  // Merge new field with existing fields.
  $combined_fields = $block_content_bulk_form + $fields;
  $status = [
    'status' => [
      'id' => ViewExecutable::generateHandlerId($published_key, $fields),
      'table' => 'block_content_field_data',
      'field' => $published_key,
      'relationship' => 'none',
      'group_type' => 'group',
      'admin_label' => '',
      'label' => 'Published',
      'exclude' => FALSE,
      'alter' => [
        'alter_text' => FALSE,
        'text' => '',
        'make_link' => FALSE,
        'path' => '',
        'absolute' => FALSE,
        'external' => FALSE,
        'replace_spaces' => FALSE,
        'path_case' => 'none',
        'trim_whitespace' => FALSE,
        'alt' => '',
        'rel' => '',
        'link_class' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'nl2br' => FALSE,
        'max_length' => 0,
        'word_boundary' => TRUE,
        'ellipsis' => TRUE,
        'more_link' => FALSE,
        'more_link_text' => '',
        'more_link_path' => '',
        'strip_tags' => FALSE,
        'trim' => FALSE,
        'preserve_tags' => '',
        'html' => FALSE,
      ],
      'element_type' => '',
      'element_class' => '',
      'element_label_type' => '',
      'element_label_class' => '',
      'element_label_colon' => TRUE,
      'element_wrapper_type' => '',
      'element_wrapper_class' => '',
      'element_default_classes' => TRUE,
      'empty' => '',
      'hide_empty' => FALSE,
      'empty_zero' => FALSE,
      'hide_alter_empty' => TRUE,
      'click_sort_column' => 'value',
      'type' => 'boolean',
      'settings' => [
        'format' => 'yes-no',
        'format_custom_true' => '',
        'format_custom_false' => '',
      ],
      'group_column' => 'value',
      'group_columns' => [],
      'group_rows' => TRUE,
      'delta_limit' => 0,
      'delta_offset' => 0,
      'delta_reversed' => FALSE,
      'delta_first_last' => FALSE,
      'multi_type' => 'separator',
      'separator' => '',
      'field_api_classes' => FALSE,
      'entity_type' => 'block_content',
      'entity_field' => $published_key,
      'plugin_id' => 'field',
    ],
  ];
  $fields_to_set = $combined_fields;
  if (!$status_field_exists) {
    // Insert the status field before the 'operations' field.
    $position = array_search('operations', array_keys($combined_fields));
    $fields_to_set = array_merge(array_slice($combined_fields, 0, $position), $status, array_slice($combined_fields, $position));
  }
  $view->set('display.default.display_options.fields', $fields_to_set);

  // Add exposed status filter.
  $status_filter = [
    'id' => ViewExecutable::generateHandlerId($published_key, $view->get("display.default.display_options.filters")),
    'table' => 'block_content_field_data',
    'field' => $published_key,
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'operator' => '',
    'value' => 'All',
    'group' => 1,
    'exposed' => TRUE,
    'expose' => [
      'operator_id' => '',
      'label' => 'Published',
      'description' => '',
      'use_operator' => FALSE,
      'operator' => 'status_op',
      'identifier' => 'status',
      'required' => FALSE,
      'remember' => FALSE,
      'multiple' => FALSE,
      'remember_roles' => [
        'authenticated' => 'authenticated',
        'anonymous' => '0',
        'administrator' => '0',
      ],
    ],
    'is_grouped' => FALSE,
    'group_info' => [
      'label' => '',
      'description' => '',
      'identifier' => '',
      'optional' => TRUE,
      'widget' => 'select',
      'multiple' => FALSE,
      'remember' => FALSE,
      'default_group' => 'All',
      'default_group_multiple' => [],
      'group_items' => [],
    ],
    'entity_type' => 'block_content',
    'entity_field' => $published_key,
    'plugin_id' => 'boolean',
  ];
  $view->set('display.default.display_options.filters.status', $status_filter);
  $view->save(TRUE);

  // Add publish action.
  /** @var \Drupal\Core\Config\Config $publish_action */
  $publish_action = \Drupal::service('config.factory')->getEditable('system.action.block_content_publish_action');
  $config_data_publish = [
    "langcode" => "en",
    "status" => TRUE,
    "dependencies" => [
      "module" => [
        "block_content",
      ],
    ],
    "id" => "block_content_publish_action",
    "label" => "Publish block content",
    "type" => "block_content",
    "plugin" => "entity:publish_action:block_content",
    "configuration" => [],
  ];
  $publish_action->setData($config_data_publish);
  $publish_action->save(TRUE);

  // Add unpublish action.
  /** @var \Drupal\Core\Config\Config $unpublish_action */
  $unpublish_action = \Drupal::service('config.factory')->getEditable('system.action.block_content_unpublish_action');
  $config_data_unpublish = [
    "langcode" => "en",
    "status" => TRUE,
    "dependencies" => [
      "module" => [
        "block_content",
      ],
    ],
    "id" => "block_content_unpublish_action",
    "label" => "Unpublish block content",
    "type" => "block_content",
    "plugin" => "entity:unpublish_action:block_content",
    "configuration" => [],
  ];
  $unpublish_action->setData($config_data_unpublish);
  $unpublish_action->save(TRUE);
}
