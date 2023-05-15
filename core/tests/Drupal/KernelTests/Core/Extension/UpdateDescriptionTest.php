<?php

namespace Drupal\KernelTests\Core\Extension;

use Drupal\Core\Update\Update;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for update descriptions.
 *
 * @group Core
 */
class UpdateDescriptionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update_test_description'];

  /**
   * Tests the list of pending database updates.
   *
   * @see \Drupal\Core\Update\Update::getList()
   */
  public function testUpdateGetUpdateList() {
    \Drupal::service('update.update_hook_registry')->setInstalledVersion('update_test_description', 8000);
    \Drupal::moduleHandler()->loadInclude('update_test_description', 'install');

    $updates = \Drupal::service(Update::class)->getList();
    $expected = [
      'pending' => [
        8001 => '8001 - Update test of slash in description and/or.',
        8002 => '8002 - Update test with multiline description, the quick brown fox jumped over the lazy dog.',
      ],
      'start' => 8001,
    ];
    $this->assertEquals($expected, $updates['update_test_description']);
  }

}
