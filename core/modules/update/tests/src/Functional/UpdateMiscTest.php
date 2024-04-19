<?php

namespace Drupal\Tests\update\Functional;

/**
 * Tests functionality of Update module not specific to project recommendations.
 *
 * @group update
 */
class UpdateMiscTest extends UpdateTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $setting = [
      '#all' => [
        'version' => '8.0.0',
      ],
    ];
    $this->config('update_test.settings')->set('system_info', $setting)->save();
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Ensures that the local actions appear.
   */
  public function testLocalActions(): void {
    $admin_user = $this->drupalCreateUser([
      'administer site configuration',
      'administer modules',
      'administer software updates',
      'administer themes',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/modules');
    $this->clickLink('Add new module');
    $this->assertSession()->addressEquals('admin/modules/install');

    $this->drupalGet('admin/appearance');
    $this->clickLink('Add new theme');
    $this->assertSession()->addressEquals('admin/theme/install');

    $this->drupalGet('admin/reports/updates');
    $this->clickLink('Add new module or theme');
    $this->assertSession()->addressEquals('admin/reports/updates/install');
  }

  /**
   * Checks that clearing the disk cache works.
   */
  public function testClearDiskCache(): void {
    $directories = [
      _update_manager_cache_directory(FALSE),
      _update_manager_extract_directory(FALSE),
    ];
    // Check that update directories do not exist.
    foreach ($directories as $directory) {
      $this->assertDirectoryDoesNotExist($directory);
    }

    // Method must not fail if update directories do not exist.
    update_clear_update_disk_cache();
  }

  /**
   * Tests the Update Manager module when the update server returns 503 errors.
   */
  public function testServiceUnavailable(): void {
    $admin_user = $this->drupalCreateUser([
      'administer site configuration',
    ]);
    $this->drupalLogin($admin_user);
    $this->refreshUpdateStatus([], '503-error');
    // Ensure that no "Warning: SimpleXMLElement..." parse errors are found.
    $this->assertSession()->pageTextNotContains('SimpleXMLElement');
    $this->assertSession()->pageTextContainsOnce('Failed to get available update data for one project.');
  }

  /**
   * Tests that exactly one fetch task per project is created and not more.
   */
  public function testFetchTasks(): void {
    $projecta = [
      'name' => 'aaa_update_test',
    ];
    $projectb = [
      'name' => 'bbb_update_test',
    ];
    $queue = \Drupal::queue('update_fetch_tasks');
    $this->assertEquals(0, $queue->numberOfItems(), 'Queue is empty');
    update_create_fetch_task($projecta);
    $this->assertEquals(1, $queue->numberOfItems(), 'Queue contains one item');
    update_create_fetch_task($projectb);
    $this->assertEquals(2, $queue->numberOfItems(), 'Queue contains two items');
    // Try to add a project again.
    update_create_fetch_task($projecta);
    $this->assertEquals(2, $queue->numberOfItems(), 'Queue still contains two items');

    // Clear storage and try again.
    update_storage_clear();
    update_create_fetch_task($projecta);
    $this->assertEquals(2, $queue->numberOfItems(), 'Queue contains two items');
  }

}
