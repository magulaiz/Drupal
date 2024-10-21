<?php

namespace Drupal\lazy_route_provider_install_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class LazyRouteProviderInstallTestHooks
{
    /**
     * Implements hook_menu_links_discovered_alter().
     */
    #[Hook('menu_links_discovered_alter')]
    public function menuLinksDiscoveredAlter(&$links)
    {
        $message = \Drupal::state()->get(__FUNCTION__, 'success');
        try {
            // Ensure that calling this does not cause a recursive rebuild.
            \Drupal::service('router.route_provider')->getAllRoutes();
        } catch (\RuntimeException) {
            $message = 'failed';
        }
        \Drupal::state()->set(__FUNCTION__, $message);
    }
}
