<?php

namespace Drupal\autowire_test;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait TestTrait {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
    );
  }

}
