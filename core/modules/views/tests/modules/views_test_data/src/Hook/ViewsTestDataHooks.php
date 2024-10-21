<?php

namespace Drupal\views_test_data\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
class ViewsTestDataHooks
{
    /**
     * Implements hook_form_BASE_FORM_ID_alter().
     */
    #[Hook('form_views_form_test_form_multiple_default_alter')]
    public function formViewsFormTestFormMultipleDefaultAlter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id)
    {
        \Drupal::messenger()->addStatus(\t('Test base form ID with Views forms and arguments.'));
    }
    /**
     * Implements hook_ENTITY_TYPE_update() for the 'view' entity type.
     */
    #[Hook('view_update')]
    public function viewUpdate(\Drupal\views\ViewEntityInterface $view)
    {
        // Use state to keep track of how many times a file is saved.
        $view_save_count = \Drupal::state()->get('views_test_data.view_save_count', []);
        $view_save_count[$view->id()] = isset($view_save_count[$view->id()]) ? $view_save_count[$view->id()] + 1 : 1;
        \Drupal::state()->set('views_test_data.view_save_count', $view_save_count);
    }
}
