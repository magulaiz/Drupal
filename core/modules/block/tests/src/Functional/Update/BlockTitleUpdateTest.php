<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the update path for block title.
 *
 * @group Update
 * @group legacy
 */
class BlockTitleUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests that title block is configured properly after update.
   *
   * @testWith ["stark", false]
   *           ["claro", true]
   *           ["olivero", false]
   */
  public function testPostUpdateAddContextualizePageTitle(string $theme, bool $contextual_title_enabled): void {
    $block_settings = $this->config('block.block.' . $theme . '_page_title')->get('settings');
    $this->assertArrayNotHasKey('base_route_title', $block_settings);

    $this->runUpdates();

    $block_settings = $this->config('block.block.' . $theme . '_page_title')->get('settings');
    $this->assertArrayHasKey('base_route_title', $block_settings);
    $this->assertSame($block_settings['base_route_title'], $contextual_title_enabled);
  }

}
