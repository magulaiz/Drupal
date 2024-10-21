<?php

namespace Drupal\theme_page_test\Hook;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Hook\Attribute\Hook;
class ThemePageTestHooks
{
    /**
     * Implements hook_system_info_alter().
     */
    #[Hook('system_info_alter')]
    public function systemInfoAlter(&$info, \Drupal\Core\Extension\Extension $file, $type)
    {
        // Make sure that all themes are visible on the Appearance form.
        if ($type === 'theme') {
            unset($info['hidden']);
        }
    }
}
