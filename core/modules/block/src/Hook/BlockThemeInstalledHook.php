<?php

namespace Drupal\block\Hook;

use Drupal\Core\Installer\InstallerKernel;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Implementations of hook_themes_installed() for block.
 */
class BlockThemeInstalledHook {

  /**
   * Implements hook_themes_installed().
   */
  #[Hook('themes_installed')]
  public function themesInstalled($theme_list): void {
    // Disable this functionality prior to install profile installation because
    // block configuration is often optional or provided by the install profile
    // itself. block_theme_initialize() will be called when the install profile is
    // installed.
    if (InstallerKernel::installationAttempted() && \Drupal::config('core.extension')->get('module.' . \Drupal::installProfile()) === NULL) {
      return;
    }

    foreach ($theme_list as $theme) {
      // Don't initialize themes that are not displayed in the UI.
      if (\Drupal::service('theme_handler')->hasUi($theme)) {
        block_theme_initialize($theme);
      }
    }
  }

}
