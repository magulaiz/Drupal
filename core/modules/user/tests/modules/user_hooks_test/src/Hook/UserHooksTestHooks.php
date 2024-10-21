<?php

namespace Drupal\user_hooks_test\Hook;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class UserHooksTestHooks
{
    /**
     * Implements hook_user_format_name_alter().
     */
    #[Hook('user_format_name_alter')]
    public function userFormatNameAlter(&$name, \Drupal\Core\Session\AccountInterface $account)
    {
        if (\Drupal::state()->get('user_hooks_test_user_format_name_alter', \FALSE)) {
            if (\Drupal::state()->get('user_hooks_test_user_format_name_alter_safe', \FALSE)) {
                $name = new \Drupal\Component\Render\FormattableMarkup('<em>@uid</em>', ['@uid' => $account->id()]);
            } else {
                $name = '<em>' . $account->id() . '</em>';
            }
        }
    }
}
