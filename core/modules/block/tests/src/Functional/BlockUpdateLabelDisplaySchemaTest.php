<?php

namespace Drupal\Tests\block\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for changing block label_display schema.
 *
 * @group Update
 */
class BlockUpdateLabelDisplaySchemaTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests block_post_update_label_display_type().
   */
  public function testBlockLabelDisplayPostUpdate() {
    $block_config = $this->config('block.block.olivero_account_menu');
    $label_display = $block_config->get('settings.label_display');
    $this->assertSame('0', $label_display);
    $this->assertIsString($label_display);

    $this->runUpdates();

    $block_config = $this->config('block.block.olivero_account_menu');
    $label_display = $block_config->get('settings.label_display');
    $this->assertFalse($label_display);
    $this->assertIsBool($label_display);
  }

}
