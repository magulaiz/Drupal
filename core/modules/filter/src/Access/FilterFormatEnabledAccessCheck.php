<?php

namespace Drupal\filter\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\editor\EditorInterface;
use Drupal\filter\FilterFormatInterface;
use Symfony\Component\Routing\Route;

/**
 * Routing requirement access check for enabled filter in a format or editor.
 */
class FilterFormatEnabledAccessCheck implements AccessInterface {

  /**
   * Checks whether a filter is enabled for a specific filter format or editor.
   *
   * @code
   * pattern: '/foo/{filter_format}'
   * requirements:
   *   _filter_format_enabled: 'my_filter'
   * @endcode
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match) {
    $expected_filter_format = $route->getRequirement('_embed_filter_format_enabled');
    assert(is_string($expected_filter_format));

    // This will work for both filter_format parameters and editor parameters.
    $editor = $route_match->getParameter('editor');
    if ($editor && $editor instanceof EditorInterface) {
      $filter_format = $editor->getFilterFormat();
    }
    else {
      $filter_format = $route_match->getParameter('filter_format');
    }
    throw new \RuntimeException(var_export($filter_format, TRUE));

    $access_result = AccessResult::allowedIf($filter_format instanceof FilterFormatInterface)
      ->addCacheableDependency($filter_format);

    if ($access_result->isAllowed()) {
      $filters = $filter_format->filters();
      return $access_result
        ->andIf(AccessResult::allowedIf($filters->has($expected_filter_format) && $filters->get($expected_filter_format)->status));
    }

    return $access_result;
  }

}
