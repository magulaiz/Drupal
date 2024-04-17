<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\DependencyInjection\Compiler\TaggedHandlersPassTest.
 */

namespace Drupal\Tests\Core\DependencyInjection\Compiler;

use Drupal\Core\DependencyInjection\Compiler\TaggedConfiguratorsPass;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\Core\DependencyInjection\Compiler\TaggedConfiguratorsPass
 * @group DependencyInjection
 */
class TaggedConfiguratorsPassTest extends UnitTestCase {

  protected function buildContainer($environment = 'dev') {
    $container = new ContainerBuilder();
    $container->setParameter('kernel.environment', $environment);
    return $container;
  }

  /**
   * Tests one consumer and two handlers.
   *
   * @covers ::process
   */
  public function testProcess() {
    $container = $this->buildContainer();
    $container->register('configured_service', ConfiguredService::class);
    // Register a second instance, this time with the class name.
    $container->register(ConfiguredService::class, ConfiguredService::class);

    $container->register('configurator_service', ConfiguratorService::class)
      ->addTag('configurator', ['method' => 'paint', 'target' => 'configured_service', 'color' => 'blue'])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'A'])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'B'])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'Y', 'priority' => 5])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'X', 'priority' => -5])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'Z', 'priority' => 3])
      // Add one that does not specify the suffix.
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service'])
      // Add one that does not specify the target.
      ->addTag('configurator', ['method' => 'append', 'suffix' => 'other'])
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'C']);

    // Register an additional configurator instance.
    $container->register('configurator_service_2', ConfiguratorService::class)
      ->addTag('configurator', ['method' => 'append', 'target' => 'configured_service', 'suffix' => 'D']);

    $handler_pass = new TaggedConfiguratorsPass();
    $handler_pass->process($container);

    $configurator = $container->getDefinition('configured_service')->getConfigurator();
    $this->assertNotNull($configurator);

    $service = $container->get('configured_service');

    $this->assertInstanceOf(ConfiguredService::class, $service);
    $this->assertSame('blue', $service->color);
    // cspell:disable
    $this->assertSame('XAB?CDZY', $service->word);
    // cspell:enable

    $service = $container->get(ConfiguredService::class);
    $this->assertInstanceOf(ConfiguredService::class, $service);
    $this->assertSame('other', $service->word);
  }

}

class ConfiguredService {

  public string $color = 'yellow';

  public string $word = '';

}

class ConfiguratorService {

  public function paint(ConfiguredService $service, string $color): void {
    $service->color = $color;
  }

  public function append(ConfiguredService $service, string $suffix = '?'): void {
    $service->word .= $suffix;
  }

}
