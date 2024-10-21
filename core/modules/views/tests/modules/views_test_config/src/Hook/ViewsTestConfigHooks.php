<?php

namespace Drupal\views_test_config\Hook;

use Drupal\views\Plugin\views\cache\CachePluginBase;
use Drupal\views\ViewExecutable;
use Drupal\Core\Hook\Attribute\Hook;
class ViewsTestConfigHooks
{
    /**
     * Implements hook_ENTITY_TYPE_load().
     */
    #[Hook('view_load')]
    public function viewLoad(array $views)
    {
        // Emulate a severely broken view: this kind of view configuration cannot be
        // saved, it can likely be returned only by a corrupt active configuration.
        $broken_view_id = \Drupal::state()->get('views_test_config.broken_view');
        if (isset($views[$broken_view_id])) {
            $display =& $views[$broken_view_id]->getDisplay('default');
            $display['display_options']['fields']['id_broken'] = \NULL;
        }
    }
    /**
     * Implements hook_views_post_render().
     */
    #[Hook('views_post_render')]
    public function viewsPostRender(\Drupal\views\ViewExecutable $view, &$output, \Drupal\views\Plugin\views\cache\CachePluginBase $cache)
    {
        if (\Drupal::state()->get('views_test_config.views_post_render_cache_tag')) {
            \Drupal::state()->set('views_test_config.views_post_render_called', \TRUE);
            // Set a cache key on output to ensure ViewsSelection::stripAdminAndAnchorTagsFromResults
            // correctly handles elements that aren't result rows.
            $output['#cache']['tags'][] = 'foo';
        }
    }
}
