<?php

namespace Drupal\mongodb\Plugin\views\field;

use Drupal\user\Plugin\views\field\Permissions as CorePermissions;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\PrerenderList;

/**
 * Overriding the views field plugin "user_permissions".
 */
class Permissions extends CorePermissions {

  use FieldPluginTrait;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    PrerenderList::init($view, $display, $options);

    $this->additional_fields['uid'] = ['table' => 'users', 'field' => 'uid'];
  }

}
