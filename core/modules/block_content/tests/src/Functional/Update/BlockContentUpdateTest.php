<?php

namespace Drupal\Tests\block_content\Functional\Update;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\block_content\Functional\BlockContentTestBulkOperationsTrait;
use Drupal\user\Entity\User;
use Drupal\views\Entity\View;

/**
 * Tests update functions for the Block Content module.
 *
 * @group block_content
 */
class BlockContentUpdateTest extends UpdatePathTestBase {

  use BlockContentTestBulkOperationsTrait;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests moving the content block library to Content.
   *
   * @see block_content_post_update_move_custom_block_library()
   */
  public function testMoveCustomBlockLibraryToContent(): void {
    $user = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/structure/block/block-content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Custom blocks');
    $this->assertSession()->pageTextContains('Custom block library');
    $this->drupalGet('admin/content/block');
    $this->assertSession()->statusCodeEquals(404);

    $this->runUpdates();

    // Load and initialize the block_content view.
    $view = View::load('block_content');
    $data = $view->toArray();
    // Check that the path, description, and menu options have been updated.
    $this->assertEquals('admin/content/block', $data['display']['page_1']['display_options']['path']);
    $this->assertEquals('Create and edit block content.', $data['display']['page_1']['display_options']['menu']['description']);
    $this->assertFalse($data['display']['page_1']['display_options']['menu']['expanded']);
    $this->assertEquals('system.admin_content', $data['display']['page_1']['display_options']['menu']['parent']);
    $this->assertEquals('Content blocks', $view->label());
    $this->assertEquals('Blocks', $data['display']['page_1']['display_options']['menu']['title']);

    // Check the new path is accessible.
    $user = $this->drupalCreateUser(['access block library']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/content/block');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests the block_content view isn't updated if the path has been modified.
   *
   * @see block_content_post_update_move_custom_block_library()
   */
  public function testCustomBlockLibraryPathOverridden(): void {
    $view = View::load('block_content');
    $display =& $view->getDisplay('page_1');
    $display['display_options']['path'] = 'some/custom/path';
    $view->save();

    $this->runUpdates();

    $view = View::load('block_content');
    $data = $view->toArray();
    $this->assertEquals('some/custom/path', $data['display']['page_1']['display_options']['path']);
  }

  /**
   * Tests the permissions are updated for users with "administer blocks".
   *
   * @see block_content_post_update_sort_permissions()
   */
  public function testBlockLibraryPermissionsUpdate(): void {
    $user = $this->drupalCreateUser(['administer blocks']);
    $this->assertTrue($user->hasPermission('administer blocks'));
    $this->assertFalse($user->hasPermission('administer block content'));
    $this->assertFalse($user->hasPermission('administer block types'));
    $this->assertFalse($user->hasPermission('access block library'));

    $this->runUpdates();

    $user = User::load($user->id());
    $this->assertTrue($user->hasPermission('administer blocks'));
    $this->assertTrue($user->hasPermission('administer block content'));
    $this->assertTrue($user->hasPermission('administer block types'));
    $this->assertTrue($user->hasPermission('access block library'));
  }

  /**
   * Tests that the block_content entity form has the status checkbox.
   *
   * @see block_content_post_update_configure_status_field_widget()
   */
  public function testStatusCheckbox() {
    $ids = \Drupal::entityQuery('entity_form_display')
      ->accessCheck(FALSE)
      ->condition('targetEntityType', 'block_content')
      ->execute();

    // Make sure we have the expected values before the update.
    $config_keys = [];
    foreach ($ids as $id) {
      $config_keys[] = 'core.entity_form_display.' . $id;
    }
    /** @var \Drupal\Core\Config\ImmutableConfig[] $form_display_configs */
    $form_display_configs = $this->container->get('config.factory')->loadMultiple($config_keys);
    foreach ($form_display_configs as $config) {
      $status_config = $config->get('block_content.published');
      $this->assertNull($status_config);
    }

    // Run updates.
    $this->runUpdates();

    /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface[] $form_displays */
    $form_displays = EntityFormDisplay::loadMultiple($ids);
    foreach ($form_displays as $form_display) {
      $component = $form_display->getComponent('status');
      if ($form_display->id() == 'block_content.basic.default') {
        // Display label should have been set to TRUE by the upgrade path.
        $this->assertEquals('boolean_checkbox', $component['type']);
        $this->assertEquals(['display_label' => TRUE], $component['settings']);
      }
    }
  }

  /**
   * Tests updating the block_content view for publishable block_content blocks.
   *
   * @see block_content_update_10201()
   */
  public function testBlockContentPublishableUIUpdate() {
    $view = View::load('block_content');
    $data = $view->toArray();
    // Check that new fields exist and that they are in the correct order.
    $view_fields = $data['display']['default']['display_options']['fields'];
    $this->assertArrayNotHasKey('block_content_bulk_form', $view_fields);
    $this->assertArrayNotHasKey('status', $view_fields);

    $this->runUpdates();
    $assert_session = $this->assertSession();

    // Load and initialize the block_content view.
    $view = View::load('block_content');
    $data = $view->toArray();
    // Check that new fields exist and that they are in the correct order.
    $view_fields = $data['display']['default']['display_options']['fields'];
    $this->assertArrayHasKey('block_content_bulk_form', $view_fields);
    $this->assertArrayHasKey('status', $view_fields);
    $block_content_bulk_form_position = array_search('block_content_bulk_form', array_keys($view_fields));
    $this->assertEquals($block_content_bulk_form_position, 0, 'The block_content_bulk_form field is in the correct position');
    $expected_status_position = array_search('operations', array_keys($view_fields)) - 1;
    $status_position = array_search('status', array_keys($view_fields));
    $this->assertEquals($status_position, $expected_status_position, 'The status field is in the correct position');
    // Check that the new filter exists and is exposed.
    $this->assertArrayHasKey('status', $data['display']['default']['display_options']['filters']);
    $this->assertTrue($data['display']['default']['display_options']['filters']['status']['exposed'], 'The status filter is exposed');

    // Check the new actions were created and work as expected.
    $user = $this->drupalCreateUser([
      'administer blocks',
      'administer block content',
      'access block library',
      'administer block_content display',
    ]);
    $this->drupalLogin($user);

    // Create a block.
    $block_title = 'Test Block';
    BlockContent::create([
      'info' => $block_title,
      'type' => 'basic',
      'body' => [
        'value' => $this->randomMachineName(16),
        'format' => 'plain_text',
      ],
    ])->save();

    // Check that the new block is displayed and showing its published status.
    $this->drupalGet('admin/content/block');
    $this->assertSession()->statusCodeEquals(200);
    $assert_session->pageTextContains($block_title);
    $this->assertBlockStatusDisplayedAs(TRUE);
  }

}
