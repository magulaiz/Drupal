<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Functional;

use Drupal\block\Entity\Block;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @covers block_post_update_move_custom_block_library
 * @group block
 */
class BlockConditionalLogicUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../system/tests/fixtures/update/drupal-11.1.2.bare.standard.php.gz',
    ];
  }

  /**
   * Tests update path for blocks' `condition_logic` property.
   */
  public function testRunUpdates(): void {
    // Find a block and check the condition_logic value.
    /** @var \Drupal\Core\Database\Connection $database */
    $database = $this->container->get('database');
    $block = $database->select('config', 'c')
      ->fields('c', ['data'])
      ->condition('name', 'block.block.claro_content')
      ->execute()
      ->fetchField();
    $block = unserialize($block);
    $block['condition_logic'] = NULL;
    $database->update('config')
      ->fields([
        'data' => serialize($block),
      ])
      ->condition('name', 'block.block.claro_content')
      ->execute();

    $this->assertNull(Block::load('claro_content')->get('condition_logic'));
    $this->runUpdates();
    $this->assertSame('and', Block::load('claro_content')->get('settings')['condition_logic']);
  }

}
