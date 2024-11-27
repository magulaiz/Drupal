<?php

namespace Drupal\menu_ui\Plugin\Menu\LocalAction;

use Drupal\Core\Menu\LocalActionWithDestination;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Routing\RouteProviderInterface;

/**
 * Modifies the 'Add link' local action to add a destination.
 *
 * @deprecated in drupal:9.3.0 and is removed from drupal:10.0.0. Use
 *   \Drupal\Core\Menu\LocalActionWithDestination instead.
 *
 * @see https://www.drupal.org/project/drupal/issues/2762131
 */
class MenuLinkAdd extends LocalActionWithDestination {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, RedirectDestinationInterface $redirectDestination) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider, $redirectDestination);

    @trigger_error('The ' . __NAMESPACE__ . '\MenuLinkAdd is deprecated. Instead, use \Drupal\Core\Menu\LocalActionWithDestination. See https://www.drupal.org/project/drupal/issues/2762131', E_USER_DEPRECATED);
  }

}
