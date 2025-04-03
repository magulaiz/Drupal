<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Functional;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\layout_builder\Traits\EnableLayoutBuilderTrait;
use Drupal\Tests\layout_builder\Traits\LayoutBuilderTestTrait;

/**
 * Inline block tests for Layout Builder.
 *
 * @group layout_builder
 */
class InlineBlockTest extends BrowserTestBase {

  use EnableLayoutBuilderTrait;
  use LayoutBuilderTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'contextual',
    'block_content',
    'layout_builder',
    'block',
    'node',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setupTestContent();
    $this->blockStorage = $this->container->get('entity_type.manager')->getStorage('block_content');
  }

  /**
   * Tests entity blocks revisioning.
   */
  public function testInlineBlocksRevisioning(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'administer node display',
      'administer node fields',
      'administer nodes',
      'bypass node access',
      'create and edit custom blocks',
      'access contextual links',
    ]));

    // Enable layout builder and overrides.
    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $this->drupalGet('node/1/layout');
    $node = Node::load(1);

    // Add an inline block.
    $component = $this->addInlineBlockToLayout($node, 'basic', 'Block title', 'The DEFAULT block body');
    $this->drupalGet('node/1');

    $assert_session->pageTextContains('The DEFAULT block body');

    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = $this->container->get(EntityTypeManagerInterface::class)->getStorage('node');
    $original_revision_id = $node_storage->getLatestRevisionId(1);

    // Create a new revision.
    $this->drupalGet('node/1/edit');
    $this->submitForm([
      'title[0][value]' => 'Node updated',
    ], 'Save');

    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The DEFAULT block body');

    // @todo clear the tempstore properly rather than manually discarding
    // changes.
    $this->drupalGet('node/1/layout/discard-changes');
    $this->submitForm([], 'Confirm');
    $this->drupalGet('layout_builder/update/block/overrides/node.1/0/content/' . $component->getUuid());
    $this->submitForm([
      'settings[block_form][body][0][value]' => 'The NEW block body',
    ], 'Update');
    $this->submitForm([], 'Save layout');

    $assert_session->linkExists('Revisions');

    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The NEW block body');
    $assert_session->pageTextNotContains('The DEFAULT block body');

    $revision_url = "node/1/revisions/$original_revision_id";

    // Ensure viewing the previous revision shows the previous block revision.
    $this->drupalGet("$revision_url/view");
    $assert_session->pageTextContains('The DEFAULT block body');
    $assert_session->pageTextNotContains('The NEW block body');

    // Revert to first revision.
    $revision_url = "$revision_url/revert";
    $this->drupalGet($revision_url);
    $page->pressButton('Revert');

    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The DEFAULT block body');
    $assert_session->pageTextNotContains('The NEW block body');
  }

  /**
   * Tests entity blocks revisioning.
   */
  public function testInlineBlocksRevisioningIntegrity(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'view all revisions',
      'access content',
      'create and edit custom blocks',
    ]));
    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $node = Node::load(1);
    // Add two blocks to the page and assert the content in each.
    $this->drupalGet('node/1/layout');
    $this->addInlineBlockViaUi('node', '1', 'content', 0, 'basic', 'Block 1', 'Block 1 original', FALSE);
    $this->addInlineBlockViaUi('node', '1', 'content', 0, 'basic', 'Block 2', 'Block 2 original');

    $this->assertNodeRevisionContent(3, ['Block 1 original', 'Block 2 original']);
    $this->assertBlockRevisionCountByTitle('Block 1', 1);
    $this->assertBlockRevisionCountByTitle('Block 2', 1);

    // Update the contents of one of the blocks and assert the updated content
    // appears on the next revision.
    $this->drupalGet('node/1/layout');
    $uuid = $this->getComponentUuidFromPlaceholderLabel('Block 2');
    $this->drupalGet('layout_builder/update/block/overrides/node.1/0/content/' . $uuid);
    $this->submitForm([
      'settings[block_form][body][0][value]' => 'Block 2 updated',
    ], 'Update');
    $this->submitForm([], 'Save layout');

    $this->assertNodeRevisionContent(4, ['Block 1 original', 'Block 2 updated']);
    $this->assertBlockRevisionCountByTitle('Block 1', 1);
    $this->assertBlockRevisionCountByTitle('Block 2', 2);

    // Update block 1 without creating a new revision of the parent.
    // @todo clear the tempstore properly rather than manually discarding
    // changes.
    $this->drupalGet('node/1/layout/discard-changes');
    $this->submitForm([], 'Confirm');
    $this->drupalGet('node/1/layout');
    $uuid = $this->getComponentUuidFromPlaceholderLabel('Block 1');
    $this->drupalGet('layout_builder/update/block/overrides/node.1/0/content/' . $uuid);
    $this->submitForm([
      'settings[block_form][body][0][value]' => 'Block 1 updated',
    ], 'Update');
    $this->drupalGet('node/1/layout');
    $this->submitForm([], 'Save layout');
    $node->setNewRevision(FALSE);
    $node->save();
    $this->assertNodeRevisionContent(5, ['Block 1 updated', 'Block 2 updated']);
    $this->assertBlockRevisionCountByTitle('Block 1', 2);
    $this->assertBlockRevisionCountByTitle('Block 2', 2);

    // Reassert all of the parent revisions contain the correct block content
    // and the integrity of the revisions was preserved.
    $this->assertNodeRevisionContent(3, ['Block 1 original', 'Block 2 original']);
  }

  /**
   * Tests that entity blocks deleted correctly.
   */
  public function testDeletion(): void {
    $prefix = 'admin/structure/types/manage/bundle_with_section_field';
    /** @var \Drupal\Core\Cron $cron */
    $cron = \Drupal::service('cron');
    /** @var \Drupal\layout_builder\InlineBlockUsageInterface $usage */
    $usage = \Drupal::service('inline_block.usage');
    $this->drupalLogin($this->drupalCreateUser([
      'administer content types',
      'access contextual links',
      'configure any layout',
      'administer node display',
      'administer node fields',
      'administer nodes',
      'bypass node access',
      'create and edit custom blocks',
    ]));
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Enable layout builder.
    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);
    // Add a block to default layout.
    $this->drupalGet($prefix . '/display/default');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals($prefix . '/display/default/layout');
    $this->clickLink('Add block');
    $this->clickLink('Create content block');
    $this->submitForm([
      'settings[label]' => 'Block title',
      'settings[block_form][body][0][value]' => 'The DEFAULT block body',
    ], 'Add block');
    $this->submitForm([], 'Save layout');

    $this->assertCount(1, $this->blockStorage->loadMultiple());
    $default_block_id = $this->getLatestBlockEntityId();

    // Ensure the block shows up on node pages.
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The DEFAULT block body');
    $this->drupalGet('node/2');
    $assert_session->pageTextContains('The DEFAULT block body');

    // Ensure we have 2 copies of the block in node overrides.
    $this->drupalGet('node/1/layout');
    $this->submitForm([], 'Save layout');
    $node_1_block_id = $this->getLatestBlockEntityId();

    $this->drupalGet('node/2/layout');
    $this->submitForm([], 'Save layout');
    $node_2_block_id = $this->getLatestBlockEntityId();
    $this->assertCount(3, $this->blockStorage->loadMultiple());

    $this->drupalGet($prefix . '/display/default/layout');

    $this->assertNotEmpty($this->blockStorage->load($default_block_id));
    $this->assertNotEmpty($usage->getUsage($default_block_id));
    // Remove block from default.
    $this->removeInlineBlockViaUi('Block title', 'defaults', 'node.bundle_with_section_field.default', 'content', 0);
    // Ensure the block in the default was deleted.
    $this->blockStorage->resetCache([$default_block_id]);
    $this->assertEmpty($this->blockStorage->load($default_block_id));
    // Ensure other blocks still exist.
    $this->assertCount(2, $this->blockStorage->loadMultiple());
    $this->assertEmpty($usage->getUsage($default_block_id));

    $this->drupalGet('node/1/layout');
    $assert_session->pageTextContains('The DEFAULT block body');

    $this->removeInlineBlockViaUi('Block title', 'overrides', 'node.1', 'content', 0);
    $cron->run();
    // Ensure entity block is not deleted because it is needed in revision.
    $this->assertNotEmpty($this->blockStorage->load($node_1_block_id));
    $this->assertCount(2, $this->blockStorage->loadMultiple());

    $this->assertNotEmpty($usage->getUsage($node_1_block_id));
    // Ensure entity block is deleted when node is deleted.
    $this->drupalGet('node/1/delete');
    $page->pressButton('Delete');
    $this->assertEmpty(Node::load(1));
    $cron->run();
    $this->assertEmpty($this->blockStorage->load($node_1_block_id));
    $this->assertEmpty($usage->getUsage($node_1_block_id));
    $this->assertCount(1, $this->blockStorage->loadMultiple());

    // Add another block to the default.
    $this->drupalGet($prefix . '/display/default');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals($prefix . '/display/default/layout');
    $this->clickLink('Add block');
    $this->clickLink('Create content block');
    $this->submitForm([
      'settings[label]' => 'Title 2',
      'settings[block_form][body][0][value]' => 'Body 2',
    ], 'Add block');
    $this->submitForm([], 'Save layout');
    $cron->run();
    $default_block2_id = $this->getLatestBlockEntityId();
    $this->assertCount(2, $this->blockStorage->loadMultiple());

    // Delete the other node so bundle can be deleted.
    $this->assertNotEmpty($usage->getUsage($node_2_block_id));
    $this->drupalGet('node/2/delete');
    $page->pressButton('Delete');
    $this->assertEmpty(Node::load(2));
    $cron->run();
    // Ensure entity block was deleted.
    $this->assertEmpty($this->blockStorage->load($node_2_block_id));
    $this->assertEmpty($usage->getUsage($node_2_block_id));
    $this->assertCount(1, $this->blockStorage->loadMultiple());

    // Delete the bundle which has the default layout.
    $this->assertNotEmpty($usage->getUsage($default_block2_id));
    $this->drupalGet($prefix . '/delete');
    $page->pressButton('Delete');
    $cron->run();

    // Ensure the entity block in default is deleted when bundle is deleted.
    $this->assertEmpty($this->blockStorage->load($default_block2_id));
    $this->assertEmpty($usage->getUsage($default_block2_id));
    $this->assertCount(0, $this->blockStorage->loadMultiple());
  }

  /**
   * Tests access to the block edit form of inline blocks.
   *
   * This module does not provide links to these forms but in case the paths are
   * accessed directly they should accessible by users with the
   * 'configure any layout' permission.
   *
   * @see layout_builder_block_content_access()
   */
  public function testInlineBlockAccess(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'administer node fields',
      'create and edit custom blocks',
    ]));
    $assert_session = $this->assertSession();

    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    // Ensure we have 2 copies of the block in node overrides.
    $this->drupalGet('node/1/layout');
    $this->addInlineBlockViaUi('node', '1', 'content', 0, 'basic', 'Block title', 'Block body');
    $node_1_block_id = $this->getLatestBlockEntityId();

    $this->drupalGet("admin/content/block/1");
    $this->drupalGet("admin/content/block/$node_1_block_id");
    $assert_session->pageTextNotContains('You are not authorized to access this page');

    $this->drupalLogout();
    $this->drupalLogin($this->drupalCreateUser([
      'administer nodes',
    ]));

    $this->drupalGet("admin/content/block/$node_1_block_id");
    $assert_session->pageTextContains('You are not authorized to access this page');

    $this->drupalLogin($this->drupalCreateUser([
      'create and edit custom blocks',
    ]));
    $this->drupalGet("admin/content/block/$node_1_block_id");
    $assert_session->pageTextNotContains('You are not authorized to access this page');
  }

  /**
   * Tests the workflow for adding an inline block depending on number of types.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddInlineBlockWorkFlow(): void {
    $assert_session = $this->assertSession();

    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $type_storage = $this->container->get('entity_type.manager')->getStorage('block_content_type');
    foreach ($type_storage->loadByProperties() as $type) {
      $type->delete();
    }

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'administer node fields',
      'create and edit custom blocks',
    ]));

    $this->drupalGet('layout_builder/choose/block/defaults/node.bundle_with_section_field.default/0/content');
    // Confirm that with no block content types the link does not appear.
    $assert_session->linkNotExists('Create content block');

    $this->createBlockContentType('basic', 'Basic block');

    $this->drupalGet('layout_builder/choose/block/defaults/node.bundle_with_section_field.default/0/content');
    // Confirm with only 1 type the "Create content block" link goes directly t
    // block add form.
    $this->clickLink('Create content block');
    $assert_session->fieldExists('Title');

    $this->createBlockContentType('advanced', 'Advanced block');

    $this->drupalGet('layout_builder/choose/block/defaults/node.bundle_with_section_field.default/0/content');
    // Confirm that, when more than 1 type exists, "Create content block" shows
    // a list of block types.
    $assert_session->linkNotExists('Basic block');
    $assert_session->linkNotExists('Advanced block');
    $this->clickLink('Create content block');
    $assert_session->fieldNotExists('Title');
    $assert_session->linkExists('Basic block');
    $assert_session->linkExists('Advanced block');

    $this->clickLink('Advanced block');
    $assert_session->fieldExists('Title');
  }

  /**
   * Tests the 'create and edit content blocks' permission to add a new block.
   */
  public function testAddInlineBlocksPermission(): void {
    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $assert = function ($permissions, $expected) {
      $assert_session = $this->assertSession();

      $this->drupalLogin($this->drupalCreateUser($permissions));
      $this->drupalGet('layout_builder/choose/block/defaults/node.bundle_with_section_field.default/0/content');
      if ($expected) {
        $assert_session->linkExists('Create content block');
      }
      else {
        $assert_session->linkNotExists('Create content block');
      }
    };

    $permissions = [
      'configure any layout',
      'administer node display',
    ];
    $assert($permissions, FALSE);
    $permissions[] = 'create and edit custom blocks';
    $assert($permissions, TRUE);
  }

  /**
   * Tests 'create and edit custom blocks' permission to edit an existing block.
   */
  public function testEditInlineBlocksPermission(): void {
    $prefix = 'admin/structure/types/manage/bundle_with_section_field';

    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'create and edit custom blocks',
    ]));
    $this->drupalGet('layout_builder/choose/block/defaults/node.bundle_with_section_field.default/0/content');
    $this->clickLink('Create content block');
    $this->submitForm([
      'settings[label]' => 'The block label',
      'settings[block_form][body][0][value]' => 'The body value',
    ], 'Add block');
    $this->submitForm([], 'Save layout');
    $assert = function ($permissions, $expected) use ($prefix) {
      $assert_session = $this->assertSession();

      $this->drupalLogin($this->drupalCreateUser($permissions));
      $this->drupalGet($prefix . '/display/default/layout');
      $uuid = $this->getComponentUuidFromPlaceholderLabel('The block label');
      $this->drupalGet('layout_builder/update/block/defaults/node.bundle_with_section_field.default/0/content/' . $uuid);
      if ($expected) {
        $assert_session->fieldExists('settings[block_form][body][0][value]');
      }
      else {
        $assert_session->fieldNotExists('settings[block_form][body][0][value]');
      }
    };

    $permissions = [
      'access contextual links',
      'configure any layout',
      'administer node display',
    ];
    $assert($permissions, FALSE);
    $permissions[] = 'create and edit custom blocks';
    $assert($permissions, TRUE);
  }

  /**
   * Test editing inline blocks when the parent has been reverted.
   */
  public function testInlineBlockParentRevert(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'administer node fields',
      'administer nodes',
      'bypass node access',
      'create and edit custom blocks',
    ]));

    $display = LayoutBuilderEntityViewDisplay::load('node.bundle_with_section_field.default');
    $this->enableLayoutBuilder($display);

    $test_node = $this->createNode([
      'title' => 'test node',
      'type' => 'bundle_with_section_field',
    ]);

    $this->addInlineBlockToLayout($test_node, 'basic', 'Example block', 'original content');
    $original_content_revision_id = Node::load($test_node->id())->getLoadedRevisionId();
    $default_block_id = $this->getLatestBlockEntityId();

    $this->drupalGet("node/{$test_node->id()}/layout");
    $uuid = $this->getComponentUuidFromPlaceholderLabel('Basic block');
    $this->drupalGet('layout_builder/update/block/overrides/node.3/0/content/' . $uuid);
    $this->submitForm([
      'settings[block_form][body][0][value]' => 'updated content',
    ], 'Update');
    $this->submitForm([], 'Save layout');

    $this->drupalGet("node/{$test_node->id()}/revisions/$original_content_revision_id/revert");
    $this->submitForm([], 'Revert');

    // Reset the changed time on the block so it can be resaved.
    // @todo track down why this is necessary.
    $block = $this->blockStorage->load($default_block_id);
    $block->setChangedTime($block->getChangedTime() - 1000)->save();

    $this->drupalGet('layout_builder/update/block/overrides/node.3/0/content/' . $uuid);
    $this->submitForm([
      'settings[block_form][body][0][value]' => 'second updated content',
    ], 'Update');
    $this->submitForm([], 'Save layout');

    $this->drupalGet($test_node->toUrl());
    $this->assertSession()->pageTextContains('second updated content');
  }

  /**
   * Assert the contents of a node revision.
   *
   * @param int $revision_id
   *   The revision ID to assert.
   * @param array $content
   *   The content items to assert on the page.
   *
   * @internal
   */
  protected function assertNodeRevisionContent(int $revision_id, array $content): void {
    $this->drupalGet("node/1/revisions/$revision_id/view");
    foreach ($content as $content_item) {
      $this->assertSession()->pageTextContains($content_item);
    }
  }

  /**
   * Assert the number of block content revisions by the block title.
   *
   * @param string $block_title
   *   The block title.
   * @param int $expected_revision_count
   *   The revision count.
   *
   * @internal
   */
  protected function assertBlockRevisionCountByTitle(string $block_title, int $expected_revision_count): void {
    $actual_revision_count = $this->blockStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('info', $block_title)
      ->allRevisions()
      ->count()
      ->execute();
    $this->assertEquals($expected_revision_count, $actual_revision_count);
  }

  /**
   * Gets the latest block entity id.
   */
  protected function getLatestBlockEntityId(): string {
    $block_ids = \Drupal::entityQuery('block_content')
      ->accessCheck(FALSE)
      ->sort('id', 'DESC')
      ->range(0, 1)
      ->execute();
    $block_id = array_pop($block_ids);
    $this->assertNotEmpty($this->blockStorage->load($block_id));
    return $block_id;
  }

  /**
   * Creates a block content type.
   *
   * @param string $id
   *   The block type id.
   * @param string $label
   *   The block type label.
   */
  protected function createBlockContentType($id, $label): void {
    $bundle = BlockContentType::create([
      'id' => $id,
      'label' => $label,
      'revision' => 1,
    ]);
    $bundle->save();
    block_content_add_body_field($bundle->id());
  }

}
