<?php

namespace Drupal\user\Hook;

use Drupal\Core\Hook\Hook;

/**
 * Implementations of hooks by the user module.
 */
class UserViewsHooks {

  /**
   * Implements hook_views_plugins_argument_validator_alter().
   */
  #[Hook('views_plugins_argument_validator_alter')]
    public function userViewsPluginsArgumentValidatorAlter(array &$plugins) {
    $plugins['entity:user']['title'] = \t('User ID');
    $plugins['entity:user']['class'] = 'Drupal\user\Plugin\views\argument_validator\User';
    $plugins['entity:user']['provider'] = 'user';
    }

}
