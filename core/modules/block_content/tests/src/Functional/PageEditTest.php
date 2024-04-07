<?php

declare(strict_types=1);

namespace Drupal\Tests\block_content\Functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;
use Drupal\user\Entity\Role;

/**
 * Create a block and test block edit functionality.
 *
 * @group block_content
 */
class PageEditTest extends BlockContentTestBase {

  use AssertBreadcrumbTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  protected static $modules = ['layout_builder', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('system_breadcrumb_block');
  }

  /**
   * Checks block edit functionality.
   */
  public function testPageEdit() {
    $this->drupalLogin($this->adminUser);

    $title_key = 'info[0][value]';
    $body_key = 'body[0][value]';
    // Create block to edit.
    $edit = [];
    $edit['info[0][value]'] = $this->randomMachineName(8);
    $edit[$body_key] = $this->randomMachineName(16);
    $this->drupalGet('block/add/basic');
    $this->submitForm($edit, 'Save');

    // Check that the block exists in the database.
    $blocks = \Drupal::entityQuery('block_content')
      ->accessCheck(FALSE)
      ->condition('info', $edit['info[0][value]'])
      ->execute();
    $block = BlockContent::load(reset($blocks));
    $this->assertNotEmpty($block, 'Content block found in database.');

    // Load the edit page.
    $this->drupalGet('admin/content/block/' . $block->id());
    $this->assertSession()->fieldValueEquals($title_key, $edit[$title_key]);
    $this->assertSession()->fieldValueEquals($body_key, $edit[$body_key]);

    // Edit the content of the block.
    $edit = [];
    $edit[$title_key] = $this->randomMachineName(8);
    $edit[$body_key] = $this->randomMachineName(16);
    // Stay on the current page, without reloading.
    $this->submitForm($edit, 'Save');

    // Edit the same block, creating a new revision.
    $this->drupalGet("admin/content/block/" . $block->id());
    $edit = [];
    $edit['info[0][value]'] = $this->randomMachineName(8);
    $edit[$body_key] = $this->randomMachineName(16);
    $edit['revision'] = TRUE;
    $this->submitForm($edit, 'Save');

    // Ensure that the block revision has been created.
    \Drupal::entityTypeManager()->getStorage('block_content')->resetCache([$block->id()]);
    $revised_block = BlockContent::load($block->id());
    $this->assertNotSame($block->getRevisionId(), $revised_block->getRevisionId(), 'A new revision has been created.');

    // Test deleting the block.
    $this->drupalGet("admin/content/block/" . $revised_block->id());
    $this->clickLink('Delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete the content block ' . $revised_block->label() . '?');

    // Test breadcrumb.
    $trail = [
      '' => 'Home',
      'admin/content/block' => 'Content blocks',
      'admin/content/block/' . $revised_block->id() => $revised_block->label(),
    ];
    $this->assertBreadcrumb(
      'admin/content/block/' . $revised_block->id() . '/delete', $trail
    );
  }

  public function testLayoutBuilderEditing(): void {
    // Create a new role for additional permissions needed.
    $role = Role::create([
      'id' => 'layout_builder_tester',
      'label' => 'Layout Builder Tester',
    ]);
    $role->grantPermission('administer block_content display')
      ->grantPermission('configure any layout')
      ->grantPermission('view the administration theme')
      ->grantPermission('edit any basic block content')
      ->save();
    $this->adminUser->addRole($role->id());
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);

    // Set a different theme for the admin pages. So we can assert the theme
    // in Layout Builder is not the same as the admin theme.
    \Drupal::service('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('admin', 'claro')->save();

    // Enable layout builder for the block content display.
    $this->drupalGet('admin/structure/block-content/manage/basic/display');
    $this->submitForm([
      'layout[enabled]' => TRUE,
    ], 'Save');
    $this->submitForm([
      'layout[allow_custom]' => TRUE,
    ], 'Save');
    $role->grantPermission('configure all basic block_content layout overrides');
    $role->save();

    // Create a block content and test the themes used.
    $block = $this->createBlockContent();
    $this->drupalGet('admin/content/block/' . $block->id() . '/layout');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementNotExists('css', '#block-claro-content');
    $this->drupalGet('admin/structure/block-content/manage/basic/display/default/layout');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementNotExists('css', '#block-claro-content');
    $this->drupalGet('admin/content/block/' . $block->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '#block-claro-content');

  }

}
