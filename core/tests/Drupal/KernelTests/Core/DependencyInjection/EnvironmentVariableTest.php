<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\DependencyInjection;

use Drupal\container_env_test\TestService;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests integration of the container with environment variables.
 *
 * @group DependencyInjection
 */
class EnvironmentVariableTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['container_env_test'];

  protected function setUp(): void {
    $_ENV['CONTAINER_ENV_TEST'] = 'test-variable';
    $_ENV['CONTAINER_ENV_TEST_WITH_DEFAULT2'] = 'override-default';

    $_ENV['CONTAINER_ENV_TEST_STRING'] = 'test-string';
    $_ENV['CONTAINER_ENV_TEST_INTEGER'] = 123;
    $_ENV['CONTAINER_ENV_TEST_BOOLEAN'] = true;
    parent::setUp();
  }

  public function testEnvironmentVariable(): void {
    $this->assertEquals('test-variable', $this->container->getParameter('container_env_test'));
    $this->assertEquals('some-test', $this->container->getParameter('container_env_test_with_default'));
    $this->assertEquals('override-default', $this->container->getParameter('container_env_test_with_default2'));

    $this->assertTrue(is_string($this->container->getParameter('container_env_test_string')));
    $this->assertEquals('test-string', $this->container->getParameter('container_env_test_string'));
    $this->assertTrue(is_int($this->container->getParameter('container_env_test_integer')));
    $this->assertEquals(123, $this->container->getParameter('container_env_test_integer'));
    $this->assertTrue(is_bool($this->container->getParameter('container_env_test_boolean')));
    $this->assertTrue($this->container->getParameter('container_env_test_boolean'));

    $this->assertEquals('some_secret', $this->container->getParameter('container_env_test_secret'));

    $this->assertEquals('http://10.0.0.1/project', $this->container->getParameter('container_env_test_host_path'));

    $service = \Drupal::service(TestService::class);
    assert($service instanceof TestService);
    $this->assertEquals('test-variable', $service->parameter);
  }

}
