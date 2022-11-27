<?php

namespace Drupal\Tests\Core\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines container test derivative discovery.
 */
class TestContainerDerivativeDiscovery extends TestDerivativeDiscovery implements ContainerDeriverInterface {

  /**
   * Constructs a TestContainerDerivativeDiscovery object.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $exampleService
   *   Some service.
   */
  public function __construct(
    protected EventDispatcherInterface $exampleService
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('example_service'));
  }

}
