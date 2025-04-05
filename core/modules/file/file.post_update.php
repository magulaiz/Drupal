<?php

/**
 * @file
 * Post update functions for File.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileConfigUpdater;

/**
 * Implements hook_removed_post_updates().
 */
function file_removed_post_updates(): array {
  return [
    'file_post_update_add_txt_if_allows_insecure_extensions' => '10.0.0',
    'file_post_update_add_permissions_to_roles' => '11.0.0',
    'file_post_update_add_default_filename_sanitization_configuration' => '11.0.0',
  ];
}

/**
 * Adds a value for the 'playsinline' setting of the 'file_video' formatter.
 */
function file_post_update_add_playsinline(array &$sandbox = []): ?TranslatableMarkup {
  /** @var \Drupal\Core\Config\Entity\ConfigEntityUpdater $config_entity_updater */
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  return $config_entity_updater->update($sandbox, 'entity_view_display', function (EntityViewDisplayInterface $display) {
    $needs_update = FALSE;
    $components = $display->getComponents();
    foreach ($components as $name => $component) {
      if (isset($component['type']) && $component['type'] === 'file_video') {
        $needs_update = TRUE;
        $component['settings']['playsinline'] = FALSE;
        $display->setComponent($name, $component);
      }
    }
    return $needs_update;
  });
}

/**
 * Add the preload configuration to existing media formatters.
 */
function file_post_update_preload_setting(array &$sandbox = []): void {
  $file_config_updater = \Drupal::classResolver(FileConfigUpdater::class);
  assert($file_config_updater instanceof FileConfigUpdater);
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'entity_view_display', function (EntityViewDisplayInterface $view_display) use ($file_config_updater): bool {
    return $file_config_updater->processPreloadSetting($view_display);
  });
}
