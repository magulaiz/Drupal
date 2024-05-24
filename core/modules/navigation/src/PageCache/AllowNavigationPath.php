<?php

namespace Drupal\navigation\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy for the navigation page cache service.
 *
 * This policy allows caching of requests directed to
 * /navigation/subtrees/{menu_name}/{level}/{depth}/{hash}
 * even for authenticated users.
 */
class AllowNavigationPath implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    // Note that this regular expression matches the end of pathinfo in order to
    // support multilingual sites using path prefixes.
    if (preg_match('#/navigation/subtrees/[a-z0-9-]+/\d+/\d+/[^/]+(/[^/]+)?$#', $request->getPathInfo())) {
      return static::ALLOW;
    }
  }

}
