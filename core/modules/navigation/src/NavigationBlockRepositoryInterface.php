<?php

namespace Drupal\navigation;

use Drupal\block\BlockRepositoryInterface;

/**
 * Navigation block repository interface.
 */
interface NavigationBlockRepositoryInterface extends BlockRepositoryInterface {

  /**
   * Content region of the navigation.
   */
  const REGION_CONTENT = '_navigation_content';

  /**
   * Footer region of the navigation.
   */
  const REGION_FOOTER = '_navigation_footer';

}
