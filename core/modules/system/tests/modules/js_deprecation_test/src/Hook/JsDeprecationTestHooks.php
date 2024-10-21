<?php

namespace Drupal\js_deprecation_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class JsDeprecationTestHooks
{
    /**
     * Implements hook_js_settings_alter().
     */
    #[Hook('js_settings_alter')]
    public function jsSettingsAlter(&$settings)
    {
        $settings['suppressDeprecationErrors'] = \FALSE;
    }
}
