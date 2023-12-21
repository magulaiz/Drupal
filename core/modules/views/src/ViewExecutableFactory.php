<?php

namespace Drupal\views;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\ViewsPluginManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the cache backend factory.
 */
class ViewExecutableFactory {

  /**
   * Stores the current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The views data.
   *
   * @var \Drupal\views\ViewsData
   */
  protected $viewsData;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The display plugin manager.
   *
   * @var \Drupal\views\Plugin\ViewsPluginManager
   */
  protected ViewsPluginManager $displayPluginManager;

  /**
   * Constructs a new ViewExecutableFactory.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\views\ViewsData $views_data
   *   The views data.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\views\Plugin\ViewsPluginManager|null $display_plugin_manager
   *   The plugin manager for display.
   */
  public function __construct(AccountInterface $user, RequestStack $request_stack, ViewsData $views_data, RouteProviderInterface $route_provider, ViewsPluginManager $display_plugin_manager = NULL) {
    $this->user = $user;
    $this->requestStack = $request_stack;
    $this->viewsData = $views_data;
    $this->routeProvider = $route_provider;
    if ($display_plugin_manager === NULL) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $display_plugin_manager argument is deprecated in drupal:10.3.0 and it will be required in drupal:12.0.0. See https://www.drupal.org/node/3410349', E_USER_DEPRECATED);
      $display_plugin_manager = \Drupal::service('plugin.manager.views.display');
    }
    $this->displayPluginManager = $display_plugin_manager;
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
    $view_executable = new ViewExecutable($view, $this->user, $this->viewsData, $this->routeProvider, $this->displayPluginManager);
    $view_executable->setRequest($this->requestStack->getCurrentRequest());
    return $view_executable;
  }

}
