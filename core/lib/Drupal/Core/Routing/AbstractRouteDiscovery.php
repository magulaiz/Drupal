<?php

namespace Drupal\Core\Routing;

use Symfony\Component\Routing\Route;

abstract class AbstractRouteDiscovery {

  /**
   * @return iterable<int, \Symfony\Component\Routing\RouteCollection>
   */
  abstract public function collectRoutes(): iterable;

  protected function resetGlobals(): array {
    return [
      'path' => NULL,
      'localized_paths' => [],
      'requirements' => [],
      'options' => [],
      'defaults' => [],
      'schemes' => [],
      'methods' => [],
      'host' => '',
      'condition' => '',
      'name' => '',
      'priority' => 0,
      'env' => NULL,
    ];
  }

  protected function createRoute(string $path, array $defaults, array $requirements, array $options, ?string $host, array $schemes, array $methods, ?string $condition): Route {
    // Ensure routes default to using Drupal's route compiler instead of
    // Symfony's.
    $options += [
      'compiler_class' => RouteCompiler::class,
    ];
    return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
  }

}
