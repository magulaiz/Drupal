<?php

/**
 * @file
 * Post update functions for Editor.
 */

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\ckeditor5\SmartDefaultSettings;
use Drupal\editor\EditorInterface;

/**
 * Implements hook_removed_post_updates().
 */
function editor_removed_post_updates() {
  return [
    'editor_post_update_clear_cache_for_file_reference_filter' => '9.0.0',
  ];
}

/**
 * Upgrades Text Editors using CKEditor 4 to use CKEditor 5.
 */
function editor_post_update_upgrade_ckeditor_4_to_5(&$sandbox = []) {
  $module_handler = \Drupal::service('module_handler');
  assert($module_handler instanceof ModuleHandlerInterface);

  // Do nothing if the contributed CKEditor 4 module is installed.
  if ($module_handler->moduleExists('ckeditor')) {
    $info = \Drupal::service('extension.list.module')->getExtensionInfo('ckeditor');
    // https://www.drupal.org/project/ckeditor is not in the 'Core' package.
    if (!isset($info['package']) || $info['package'] !== 'Core') {
      return 'Skipping the automatic CKEditor 4 to 5 upgrade path because the contributed CKEditor 4 module is installed. You can manually upgrade each text editor to CKEditor 5 at any time by navigating to the "Text formats and editors" and changing the text editor from CKEditor 4 to 5: this uses the same upgrade path logic.';
    }
  }

  // Install the CKEditor 5 module if it is not already installed.
  if (!$module_handler->moduleExists('ckeditor5') && !\Drupal::service('module_installer')->install(['ckeditor5'])) {
    throw new \Exception('The CKEditor 5 module could not be installed');
  }
  $ckeditor5_smart_default_settings = \Drupal::service('ckeditor5.smart_default_settings');
  $ckeditor5_plugin_manager = \Drupal::service('plugin.manager.ckeditor5.plugin');

  // Update affected Editor config entities.
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $all_messages = [];
  $config_entity_updater->update($sandbox, 'editor', function (EditorInterface $editor) use ($ckeditor5_smart_default_settings, $ckeditor5_plugin_manager, &$all_messages) : bool {
    assert($ckeditor5_smart_default_settings instanceof SmartDefaultSettings);

    // Only update Text Editor config entities that use CKEditor 4.
    if ($editor->getEditor() !== 'ckeditor') {
      return FALSE;
    }

    $format = $editor->getFilterFormat();
    [$updated_editor, $messages] = $ckeditor5_smart_default_settings->computeSmartDefaultSettings($editor, $format);
    assert($updated_editor instanceof EditorInterface);

    // Update $editor, to let ConfigEntityUpdater update it.
    $editor->setEditor($updated_editor->getEditor());
    $editor->setSettings($updated_editor->getSettings());

    // If the equivalent CKEditor 5 configuration requires addition to the list
    // of allowed HTML tags for the `filter_html` filter, apply those changes
    // too, just like ckeditor5_form_filter_format_form_alter() would.
    $allowed_tags = HTMLRestrictions::fromTextFormat($format);
    $enabled_plugins = array_keys($ckeditor5_plugin_manager->getEnabledDefinitions($updated_editor));
    $updated_allowed_tags = new HTMLRestrictions($ckeditor5_plugin_manager->getProvidedElements($enabled_plugins, $updated_editor));
    if (!$updated_allowed_tags->diff($allowed_tags)->allowsNothing()) {
      $updated_format = clone $format;
      $filter_html_config = $format->filters('filter_html')->getConfiguration();
      $filter_html_config['settings']['allowed_html'] = $updated_allowed_tags->toFilterHtmlAllowedTagsString();
      $updated_format->setFilterConfig('filter_html', $filter_html_config);
      $updated_format->trustData()->save();
    }

    // Collect all messages, to combine in a single string at the end.
    $all_messages[$editor->id()] = $messages;

    return TRUE;
  });

  // Inform the user.
  $upgrade_details = $module_handler->moduleExists('dblog')
    ? t('Additional details are available <a target="_blank" href=":dblog_url">in your logs</a>.',
      [
        ':dblog_url' => Url::fromRoute('dblog.overview')
          ->setOption('query', ['type[]' => 'ckeditor5'])
          // @todo Simplify with https://www.drupal.org/node/2548095
          ->setOption('base_url', $GLOBALS['base_url'])
          ->toString(),
      ]
    )
    : t('Additional details are available in your logs.');
  return t('Updated @count Text Editors that used CKEditor 4 to use CKEditor 5 instead (<code>@list</code>).', [
    '@count' => count($all_messages),
    '@list' => Markup::create(implode('</code>, <code>', array_keys($all_messages))),
  ]) . '<br>' . $upgrade_details;
}
