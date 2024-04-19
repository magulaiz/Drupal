<?php

namespace Drupal\navigation\Controller;

use Drupal\block\Controller\BlockListController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a controller to list navigation_blocks.
 */
class NavigationBlockListController extends BlockListController {

  /**
   * {@inheritdoc}
   */
  public function listing($theme = NULL, Request $request = NULL) {
    return $this->entityTypeManager()->getHandler('block', 'navigation_block_list')->render($request);
  }

}
