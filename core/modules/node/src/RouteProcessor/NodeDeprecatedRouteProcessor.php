<?php

namespace Drupal\node\RouteProcessor;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\Routing\Route;

/**
 * Processes the Node entity BC routes.
 *
 * @internal
 */
class NodeDeprecatedRouteProcessor implements OutboundRouteProcessorInterface {

  /**
   * The route map.
   *
   * @var array
   */
  protected array $routeMap = [
    'node.add_page' => 'entity.node.add_page',
    'node.add' => 'entity.node.add_form',
  ];

  /**
   * Constructs a NodeRouteProcessorBC object.
   */
  public function __construct(protected RouteProviderInterface $routeProvider) {
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($route_name, Route $route, array &$parameters, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (in_array($route_name, array_keys($this->routeMap), TRUE)) {
      $redirected_route_name = $this->routeMap[$route_name];
      @trigger_error(sprintf("The '%s' route is deprecated in drupal:11.1.0 and is removed in drupal:12.0.0. Use the '%s' route instead. See https://www.drupal.org/node/2940083", $route_name, $redirected_route_name), E_USER_DEPRECATED);
      static::overwriteRoute($route, $this->routeProvider->getRouteByName($redirected_route_name));
    }
  }

  /**
   * Overwrites one route's metadata with the other's.
   *
   * @param \Symfony\Component\Routing\Route $target_route
   *   The route whose metadata to overwrite.
   * @param \Symfony\Component\Routing\Route $source_route
   *   The route whose metadata to read from.
   *
   * @see \Symfony\Component\Routing\Route
   */
  protected static function overwriteRoute(Route $target_route, Route $source_route) {
    $target_route->setPath($source_route->getPath());
    $target_route->setDefaults($source_route->getDefaults());
    $target_route->setRequirements($source_route->getRequirements());
    $target_route->setOptions($source_route->getOptions());
    $target_route->setHost($source_route->getHost());
    $target_route->setSchemes($source_route->getSchemes());
    $target_route->setMethods($source_route->getMethods());
  }

}
