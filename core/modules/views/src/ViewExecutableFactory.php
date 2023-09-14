<?php

namespace Drupal\views;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the cache backend factory.
 */
class ViewExecutableFactory {

  /**
   * Constructs a new ViewExecutableFactory.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\views\ViewsData $viewsData
   *   The views data.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   */
  public function __construct(protected AccountInterface $user, protected RequestStack $requestStack, protected ViewsData $viewsData, protected RouteProviderInterface $routeProvider)
  {
  }

  /**
   * Instantiates a ViewExecutable class.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   A view entity instance.
   *
   * @return \Drupal\views\ViewExecutable
   *   A ViewExecutable instance.
   */
  public function get(ViewEntityInterface $view) {
    $view_executable = new ViewExecutable($view, $this->user, $this->viewsData, $this->routeProvider);
    $view_executable->setRequest($this->requestStack->getCurrentRequest());
    return $view_executable;
  }

}
