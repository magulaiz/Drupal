<?php

namespace Drupal\tracker\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route parameters needed to link to the current user tracker tab.
 */
class UserTrackerTab extends LocalTaskDefault implements ContainerFactoryPluginInterface {

  /**
   * Construct the UserTrackerTab object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, protected AccountInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    return ['user' => $this->currentUser->id()];
  }

}
