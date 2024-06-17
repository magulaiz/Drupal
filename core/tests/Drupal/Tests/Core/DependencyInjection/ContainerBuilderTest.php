<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\DependencyInjection;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\Tests\Core\DependencyInjection\Fixture\BarClass;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @coversDefaultClass \Drupal\Core\DependencyInjection\ContainerBuilder
 * @group DependencyInjection
 */
class ContainerBuilderTest extends UnitTestCase {

  /**
   * @covers ::get
   */
  public function testGet() {
    $container = new ContainerBuilder();
    $container->register('bar', 'Drupal\Tests\Core\DependencyInjection\Fixture\BarClass');

    $result = $container->get('bar');
    $this->assertInstanceOf(BarClass::class, $result);
  }

  /**
   * @covers ::setParameter
   */
  public function testSetParameterException() {
    $container = new ContainerBuilder();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Parameter names must be lowercase: Buzz');
    $container->setParameter('Buzz', 'buzz');
  }

  /**
   * @covers ::register
   */
  public function testRegister() {
    $container = new ContainerBuilder();
    $service = $container->register('bar');
    $this->assertTrue($service->isPublic());
  }

  /**
   * @covers ::setDefinition
   */
  public function testSetDefinition() {
    // Test a service with public set to true.
    $container = new ContainerBuilder();
    $definition = new Definition();
    $definition->setPublic(TRUE);
    $service = $container->setDefinition('foo', $definition);
    $this->assertTrue($service->isPublic());

    // Test a service with public set to false.
    $definition = new Definition();
    $definition->setPublic(FALSE);
    $service = $container->setDefinition('foo', $definition);
    $this->assertFalse($service->isPublic());
  }

  /**
   * @covers ::setAlias
   */
  public function testSetAlias() {
    // Test a service with public set to true (default).
    $container = new ContainerBuilder();
    $container->register('public');
    $alias = $container->setAlias('foo', 'public');
    $this->assertTrue($alias->isPublic());

    // Test a service with public set to false.
    $definition = new Definition();
    $definition->setPublic(FALSE);
    $container->setDefinition('private', $definition);
    $alias = $container->setAlias('foo', 'private');
    $this->assertFalse($alias->isPublic());

    // Test an alias to a public alias that links to a private service.
    $alias = new Alias('private', TRUE);
    $alias = $container->setAlias('foo', $alias);
    $this->assertTrue($alias->isPublic());

    // Test a alias to a private alias to a private service.
    $alias = new Alias('private');
    $alias = $container->setAlias('foo', $alias);
    $this->assertFalse($alias->isPublic());

    // Test a alias to a private alias to a public service.
    $alias = new Alias('public');
    $alias = $container->setAlias('foo', $alias);
    $this->assertTrue($alias->isPublic());

    // Test alias replacing a service inherits the visibility from the service
    // it is replacing and not from the aliased service.
    foreach (['public', 'private'] as $service_to_alias_to) {
      $definition->setPublic(FALSE);
      $container->setDefinition('foo', $definition);
      $alias = $container->setAlias('foo', $service_to_alias_to);
      $this->assertFalse($alias->isPublic());
      $definition->setPublic(TRUE);
      $container->setDefinition('foo', $definition);
      $alias = $container->setAlias('foo', $service_to_alias_to);
      $this->assertTrue($alias->isPublic());
    }
  }

  /**
   * Tests serialization.
   */
  public function testSerialize() {
    $container = new ContainerBuilder();
    $this->expectException(\AssertionError::class);
    serialize($container);
  }

  /**
   * Tests constructor and resource tracking disabling.
   *
   * This test runs in a separate process to ensure the aliased class does not
   * affect any other tests.
   *
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testConstructor() {
    class_alias(TestInterface::class, 'Symfony\Component\Config\Resource\ResourceInterface');
    $container = new ContainerBuilder();
    $this->assertFalse($container->isTrackingResources());
  }

}

/**
 * A test interface for testing ContainerBuilder::__construct().
 *
 * @see \Drupal\Tests\Core\DependencyInjection\ContainerBuilderTest::testConstructor()
 */
interface TestInterface {
}
