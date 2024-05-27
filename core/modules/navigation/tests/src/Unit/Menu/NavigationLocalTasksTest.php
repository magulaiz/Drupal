<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Unit\Menu;

use Drupal\Tests\Core\Menu\LocalTaskIntegrationTestBase;

/**
 * Tests existence of config local tasks.
 *
 * @group config
 */
class NavigationLocalTasksTest extends LocalTaskIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->directoryList = ['navigation' => 'core/modules/navigation'];
    parent::setUp();
  }

  /**
   * Tests config local tasks existence.
   *
   * @dataProvider getConfigAdminRoutes
   */
  public function testConfigAdminLocalTasks($route, $expected) {
    $this->assertLocalTasks($route, $expected);
  }

  /**
   * Provides a list of routes to test.
   */
  public static function getConfigAdminRoutes() {
    return [
      ['navigation.settings', [['navigation.settings', 'navigation.layout']]],
    ];
  }

}
