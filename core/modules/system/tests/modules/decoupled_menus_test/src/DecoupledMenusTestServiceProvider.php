<?php

declare(strict_types=1);

namespace Drupal\decoupled_menus_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Makes "hal_json" the default content negotiation format.
 *
 * @see \Drupal\decoupled_menus\StackMiddleware\NegotiationMiddleware
 * @see \Drupal\Tests\decoupled_menus\Functional\LinksetControllerTest::testPathAliasResolution()
 */
final class DecoupledMenusTestServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->setParameter('host.default_formats', [
      'hal_json' => ['localhost'],
    ]);
  }

}
