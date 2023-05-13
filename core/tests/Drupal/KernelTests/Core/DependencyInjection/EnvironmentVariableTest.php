<?php

namespace Drupal\KernelTests\Core\DependencyInjection;

use Drupal\KernelTests\KernelTestBase;

class EnvironmentVariableTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['container_env_test'];

  protected function setUp(): void {
    $name = $this->getName();

    $_ENV['CONTAINER_ENV_TEST'] = 'test-variable';
    $_ENV['CONTAINER_ENV_TEST_WITH_DEFAULT2'] = 'override-default';
    parent::setUp();
  }


  public function testWithEnvironmentVariable(): void {
    $this->assertEquals('test-variable', $this->container->getParameter('container_env_test'));
    $this->assertEquals('some-test', $this->container->getParameter('container_env_test_with_default'));
    $this->assertEquals('override-default', $this->container->getParameter('container_env_test_with_default2'));
  }

}
