<?php

declare(strict_types=1);

namespace Drupal\Core\Installer;

use Drupal\Core\Extension\ThemeEngineExtensionList;

/**
 * Overrides the theme engine extension list to have a static cache.
 */
class InstallerThemeEngineExtensionList extends ThemeEngineExtensionList {
  use ExtensionListTrait;

}
