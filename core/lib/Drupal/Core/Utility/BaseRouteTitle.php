<?php

namespace Drupal\Core\Utility;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Menu\LocalTaskManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Provides a class which gets title based on base route.
 */
class BaseRouteTitle {

  /**
   * Constructs a RequestGenerator object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator.
   * @param \Drupal\Core\Controller\TitleResolverInterface $titleResolver
   *   The title resolver.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\Core\Menu\LocalTaskManager $localTaskManager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Utility\RequestGenerator $requestGenerator
   *   The request generator.
   */
  public function __construct(
    protected UrlGeneratorInterface $urlGenerator,
    protected TitleResolverInterface $titleResolver,
    protected RouteMatchInterface $routeMatch,
    protected LocalTaskManager $localTaskManager,
    protected RouteProviderInterface $routeProvider,
    protected RequestStack $requestStack,
    protected RequestGenerator $requestGenerator,
  ) {
  }

  /**
   * Gets base route title.
   *
   * @return array|string|\Stringable|null
   *   The title based on base route.
   */
  public function getBaseRouteTitle(): array|string|null|\Stringable {
    $route_name = $this->routeMatch->getRouteName();
    $base_route_name = $this->localTaskManager->getBaseRouteName($route_name);
    $title = NULL;
    if ($base_route_name) {
      if ($base_route_name !== $route_name) {
        try {
          $path = $this->urlGenerator->getPathFromRoute($base_route_name, $this->routeMatch->getRawParameters()->all());
        }
        catch (RouteNotFoundException | InvalidParameterException) {
          return NULL;
        }
        $route_request = $this->requestGenerator->generateRequestForPath($path, []);
        if ($route_request) {
          $title = $this->titleResolver->getTitle($route_request, $this->routeProvider->getRouteByName($base_route_name));
        }
      }
    }
    return $title;
  }

}
