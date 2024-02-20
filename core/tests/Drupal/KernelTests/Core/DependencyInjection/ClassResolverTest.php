<?php

namespace Drupal\KernelTests\Core\DependencyInjection;

use Drupal\class_resolver_test\AutowiringFailed;
use Drupal\class_resolver_test\ContainerInjection;
use Drupal\class_resolver_test\NoConstructor;
use Drupal\class_resolver_test\NoContainerInjection;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Tests class resolver.
 *
 * @coversDefaultClass \Drupal\Core\DependencyInjection\ClassResolver
 * @group DependencyInjection
 */
class ClassResolverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['class_resolver_test'];

  /**
   * @covers ::getInstanceFromDefinition
   */
  public function testGetInstanceFromDefinition(): void {
    $class_resolver = $this->container->get(ClassResolverInterface::class);
    \assert($class_resolver instanceof ClassResolverInterface);

    $instance = $class_resolver->getInstanceFromDefinition(NoContainerInjection::class);

    $property = new \ReflectionProperty(NoContainerInjection::class, 'lock');
    $this->assertSame($this->container->get('lock'), $property->getValue($instance));
    $property = new \ReflectionProperty(NoContainerInjection::class, 'entityTypeManager');
    $this->assertSame($this->container->get(EntityTypeManagerInterface::class), $property->getValue($instance));
    $property = new \ReflectionProperty(NoContainerInjection::class, 'nullable');
    $this->assertNull($property->getValue($instance));
    $property = new \ReflectionProperty(NoContainerInjection::class, 'defaultValue');
    $this->assertEquals('foo', $property->getValue($instance));

    $instance = $class_resolver->getInstanceFromDefinition(ContainerInjection::class);

    $property = new \ReflectionProperty(ContainerInjection::class, 'lock');
    $this->assertSame($this->container->get('lock'), $property->getValue($instance));
    $property = new \ReflectionProperty(ContainerInjection::class, 'entityTypeManager');
    $this->assertSame($this->container->get(EntityTypeManagerInterface::class), $property->getValue($instance));

    $instance = $class_resolver->getInstanceFromDefinition(NoConstructor::class);

    $property = new \ReflectionProperty(NoConstructor::class, 'container');
    $this->assertSame($this->container, $property->getValue($instance));

    $instance = $class_resolver->getInstanceFromDefinition(EntityTypeManagerInterface::class);

    $this->assertSame($this->container->get(EntityTypeManagerInterface::class), $instance);
  }

  /**
   * @covers ::getInstanceFromDefinition
   */
  public function testGetInstanceArgumentException(): void {
    $class_resolver = $this->container->get(ClassResolverInterface::class);
    \assert($class_resolver instanceof ClassResolverInterface);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Class "Foo" does not exist.');
    $class_resolver->getInstanceFromDefinition('Foo');
  }

  /**
   * @covers ::getInstanceFromDefinition
   */
  public function testGetInstanceAutowiringFailedException(): void {
    $class_resolver = $this->container->get(ClassResolverInterface::class);
    \assert($class_resolver instanceof ClassResolverInterface);

    $this->expectException(AutowiringFailedException::class);
    $this->expectExceptionMessage('Cannot autowire service "Drupal\Core\Lock\LockBackendInterface": argument "$lock" of method "Drupal\class_resolver_test\AutowiringFailed::__construct()", you should configure its value explicitly.');
    $class_resolver->getInstanceFromDefinition(AutowiringFailed::class);
  }

}
