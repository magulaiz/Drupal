<?php

namespace Drupal\Core\Routing;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;

abstract class AbstractStaticRouteDiscovery implements EventSubscriberInterface {

  /**
   * @return iterable<int, \Symfony\Component\Routing\RouteCollection>
   */
  abstract protected function collectRoutes(): iterable;

  /**
   * Determines the priority of the route build event listener.
   *
   * @return int
   */
  abstract protected static function getPriority(): int;

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

  /**
   * Adds routes to the route builder.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function onRouteBuild(RouteBuildEvent $event) {
    foreach ($this->collectRoutes() as $collection) {
      $event->getRouteCollection()->addCollection($collection);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::STATIC] = ['onRouteBuild', static::getPriority()];
    return $events;
  }

}
