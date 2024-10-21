<?php

namespace Drupal\views_test_data\Hook;

use Drupal\views\ViewExecutable;
use Drupal\views\Analyzer;
use Drupal\Core\Hook\Attribute\Hook;
class ViewsTestDataViewsHooks
{
    /**
     * Implements hook_views_data().
     */
    #[Hook('views_data')]
    public function viewsData()
    {
        $state = \Drupal::service('state');
        $state->set('views_hook_test_views_data', \TRUE);
        // We use a state variable to keep track of how many times this function is
        // called so we can assert that calls to
        // \Drupal\views\ViewsData::delete() trigger a rebuild of views data.
        if (!($count = $state->get('views_test_data_views_data_count'))) {
            $count = 0;
        }
        $count++;
        $state->set('views_test_data_views_data_count', $count);
        return $state->get('views_test_data_views_data', []);
    }
    /**
     * Implements hook_views_data_alter().
     */
    #[Hook('views_data_alter')]
    public function viewsDataAlter(array &$data)
    {
        \Drupal::state()->set('views_hook_test_views_data_alter', \TRUE);
        \Drupal::state()->set('views_hook_test_views_data_alter_data', $data);
    }
    /**
     * Implements hook_views_analyze().
     */
    #[Hook('views_analyze')]
    public function viewsAnalyze(\Drupal\views\ViewExecutable $view)
    {
        \Drupal::state()->set('views_hook_test_views_analyze', \TRUE);
        $ret = [];
        $ret[] = \Drupal\views\Analyzer::formatMessage(\t('Test ok message'), 'ok');
        $ret[] = \Drupal\views\Analyzer::formatMessage(\t('Test warning message'), 'warning');
        $ret[] = \Drupal\views\Analyzer::formatMessage(\t('Test error message'), 'error');
        return $ret;
    }
    /**
     * Implements hook_views_invalidate_cache().
     */
    #[Hook('views_invalidate_cache')]
    public function viewsInvalidateCache()
    {
        \Drupal::state()->set('views_hook_test_views_invalidate_cache', \TRUE);
    }
}
