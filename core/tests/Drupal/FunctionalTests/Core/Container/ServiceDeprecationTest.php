<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Core\Container;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\BrowserTestBase;

/**
 * Test whether deprecation notices are triggered via \Drupal::service().
 *
 * Note: this test must be a BrowserTestBase so the container is properly
 * compiled. The container in KernelTestBase tests is always an instance of
 * \Drupal\Core\DependencyInjection\ContainerBuilder.
 *
 * @group Container
 * @group legacy
 */
#[CoversClass(\Drupal\Component\DependencyInjection\Container::class)]
class ServiceDeprecationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['deprecation_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public function testGetDeprecated() {
    $this->expectDeprecation('The "deprecation_test.service" service is deprecated in drupal:9.0.0 and is removed from drupal:20.0.0. This is a test.');
    $this->expectDeprecation('The "deprecation_test.alias" alias is deprecated in drupal:9.0.0 and is removed from drupal:20.0.0. This is a test.');
    // @phpstan-ignore-next-line
    \Drupal::service('deprecation_test.service');
    // @phpstan-ignore-next-line
    \Drupal::service('deprecation_test.alias');
  }

}
