<?php

namespace Drupal\system\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Dynamically defines routes for menu linkset endpoints.
 */
class MenuLinksetRoutes implements ContainerInjectionInterface {

  /**
   * The system.linkset config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new MenuLinksetRoutes object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('system.linkset');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];

    // Only enable linkset routes if the related config option is enabled.
    if ($this->config->get('enable_endpoint')) {
      $routes['system.menu.linkset'] = new Route(
        '/system/menu/{menu}/linkset',
        [
          '_controller' => 'Drupal\system\Controller\Linkset::process',
        ],
        [
          '_access' => 'TRUE',
        ],
        [
          'parameters' => [
            'menu' => [
              'type' => 'entity:menu',
            ],
          ],
        ]
      );
    }
    return $routes;
  }

}
