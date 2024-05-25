<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\UpdateSystem;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests update hook execution selection.
 *
 * Tests upgrading a major version that may have an update hook added that is
 * below the current schema version.
 *
 * @group system
 */
class UpdateNewUpdateBelowSchemaTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['update_test_0'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Only install update_test_2.module, even though its updates have a
    // dependency on update_test_3.module.
    parent::setUp();
    require_once $this->root . '/core/includes/update.inc';
  }

  /**
   * Tests that updates below the current schema will be run if new.
   */
  public function testNewUpdate(): void {
    /** @var \Drupal\Core\Update\UpdateHookRegistry $registry */
    $registry = $this->container->get('update.update_hook_registry');
    $registry->setInstalledVersion('update_test_0', 8003);
    $registry->setPreviouslyInstalledSchemaVersions('update_test_0', [8001, 8003]);
    $updates = update_get_update_list();
    $this->assertSame(8002, $updates['update_test_0']['start']);
    $this->assertCount(1, $updates['update_test_0']['pending']);
    $this->assertArrayHasKey(8002, $updates['update_test_0']['pending']);
  }

}
