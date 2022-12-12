<?php

namespace Drupal\Tests\block_content\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\views\Entity\View;

/**
 * Tests update functions for the Block Content module.
 *
 * @group Update
 * @group legacy
 */
class BlockContentUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests moving the custom block library to Content.
   *
   * @see block_content_post_update_move_custom_block_library()
   */
  public function testMoveCustomBlockLibraryToContent() {
    $user = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/structure/block/block-content');
    $this->assertSession()->statusCodeEquals(200);

    $this->runUpdates();

    // Load and initialize the block_content view.
    $view = View::load('block_content');
    $data = $view->toArray();
    // Check that new fields exist and that they are in the correct order.
    $this->assertEquals('admin/content/block-content', $data['display']['page_1']['display_options']['path']);
    $this->assertEquals('Create and edit custom block content.', $data['display']['page_1']['display_options']['menu']['description']);
    $this->assertFalse($data['display']['page_1']['display_options']['menu']['expanded']);
    $this->assertEquals('system.admin_content', $data['display']['page_1']['display_options']['menu']['parent']);

    // Check the new path is accessible.
    $user = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/content/block-content');
    $this->assertSession()->statusCodeEquals(200);
  }

}
