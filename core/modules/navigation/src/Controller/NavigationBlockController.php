<?php

namespace Drupal\navigation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\navigation\NavigationBlockInterface;

/**
 * Controller routines for navigation block routes.
 */
class NavigationBlockController extends ControllerBase {

  /**
   * Calls a method on a navigation_block and reloads the listing page.
   *
   * @param \Drupal\navigation\NavigationBlockInterface $navigation_block
   *   The navigation_block being acted upon.
   * @param string $op
   *   The operation to perform, e.g., 'enable' or 'disable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the listing page.
   */
  public function performOperation(NavigationBlockInterface $navigation_block, $op) {
    $navigation_block->$op()->save();
    $this->messenger()->addStatus($this->t('The navigation block settings have been updated.'));
    return $this->redirect('navigation_block.admin_display');
  }

}
