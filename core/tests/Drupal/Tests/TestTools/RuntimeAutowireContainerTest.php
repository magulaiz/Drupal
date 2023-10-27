<?php

namespace Drupal\Tests\TestTools;

use Drupal\Tests\Core\DependencyInjection\Fixture\BarClass;
use Drupal\Tests\TestTools\Fixture\AutowireFooClass;
use Drupal\Tests\TestTools\Fixture\AutowireFooInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\TestTools\RuntimeAutowireContainer;

/**
 * @coversDefaultClass \Drupal\TestTools\RuntimeAutowireContainer
 *
 * @group TestTools
 */
class RuntimeAutowireContainerTest extends UnitTestCase {

  public function testContainer(): void {
    $container = new RuntimeAutowireContainer();
    $container->setParameter('string $x', 'X');
    $container->addClass(BarClass::class);
    $container->addClass(AutowireFooClass::class);
    $service = $container->get(AutowireFooInterface::class);
    $this->assertInstanceOf(AutowireFooInterface::class, $service);
    $this->assertSame('X', $service->getX());
  }

}
