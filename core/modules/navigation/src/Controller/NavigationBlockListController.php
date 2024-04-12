<?php

namespace Drupal\navigation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a controller to list navigation_blocks.
 */
class NavigationBlockListController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function listing(Request $request = NULL) {
    return $this->entityTypeManager()->getListBuilder('navigation_block')->render($request);
  }

}
