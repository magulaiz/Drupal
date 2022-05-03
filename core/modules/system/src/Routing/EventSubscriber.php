<?php

declare(strict_types=1);

namespace Drupal\system\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Routing event subscriber.
 *
 * Alters this module's routes to enable all authentication providers.
 *
 * @internal
 *   This class's API is internal and it is not intended for extension.
 *
 * @todo remove this service when https://www.drupal.org/project/drupal/issues/3200620 lands.
 */
final class EventSubscriber extends RouteSubscriberBase {

  /**
   * An array of enabled authentication provider IDs.
   *
   * @var string[]
   */
  protected $providerIds;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * EventSubscriber constructor.
   *
   * @param string[] $authentication_providers
   *   An array of authentication providers, keyed by ID.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $authentication_providers, ConfigFactoryInterface $config_factory) {
    $this->providerIds = array_keys($authentication_providers);
    $this->configFactory = $config_factory;
  }

  /**
   * Alter routes.
   *
   * If the endpoint is configured to be enabled, dynamically enable all
   * authentication providers on this module's routes since they cannot be known
   * in advance.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   A collection of routes.
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($this->configFactory->get('system.linkset')->get('enable_endpoint')) {
      $collection->get('system.menu.linkset')->setOption('_auth', $this->providerIds);
    }
  }

}
