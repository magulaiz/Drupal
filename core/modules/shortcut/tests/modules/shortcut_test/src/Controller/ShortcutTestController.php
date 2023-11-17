<?php

namespace Drupal\shortcut_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides markup for the shortcut_test module.
 */
class ShortcutTestController extends ControllerBase {

  /**
   * Returns the markup for a page without a title.
   */
  public function pageNoTitle() {
    return ['#markup' => 'Shortcut test of a page with no title.'];
  }

}
