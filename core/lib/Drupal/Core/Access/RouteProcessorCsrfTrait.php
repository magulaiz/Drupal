<?php

namespace Drupal\Core\Access;

use Symfony\Component\Routing\Route;

/**
 * Provides a helper to prepare CSRF during Route processor.
 */
trait RouteProcessorCsrfTrait {

  /**
   * Prepares the route's path for generating a token.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The outbound route to process.
   * @param array $parameters
   *   An array of parameters to be passed to the route compiler.
   *
   * @return string
   *   The route's path with parameters replaced.
   */
  protected static function preparePath(Route $route, array $parameters): string {
    if ($route->getOption('_csrf_exclude_parameters')) {
      $exclude_parameters = $route->getOption('_csrf_exclude_parameters');
      foreach ($exclude_parameters as $exclude_parameter) {
        unset($parameters[$exclude_parameter]);
      }
    }

    $keys = array_map(
      function ($key) {
        return "{{$key}}";
      },
      array_keys($parameters)
    );

    // Replace the path parameters with values from the parameters array.
    $path = ltrim($route->getPath(), '/');
    return str_replace($keys, $parameters, $path);
  }

}
