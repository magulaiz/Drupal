<?php

namespace Drupal\user_form_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class UserFormTestHooks
{
    /**
     * Implements hook_form_FORM_ID_alter() for user_cancel_form().
     */
    #[Hook('form_user_cancel_form_alter')]
    public function formUserCancelFormAlter(&$form, &$form_state)
    {
        $form['user_cancel_confirm']['#default_value'] = \FALSE;
        $form['access']['#value'] = \Drupal::currentUser()->hasPermission('cancel other accounts');
    }
}
