<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

/**
 * Tests that the inline block feature works correctly.
 *
 * @group layout_builder
 * @group #slow
 */
class InlineBlockTest extends InlineBlockTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
  ];

  /**
   * Tests adding and editing of inline blocks.
   */
  public function testInlineBlocks(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'administer node fields',
      'create and edit custom blocks',
    ]));

    // Enable layout builder.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm(['layout[enabled]' => TRUE], 'Save');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals(static::FIELD_UI_PREFIX . '/display/default/layout');
    // Add a basic block with the body field set.
    $this->addInlineBlockToLayout('Block title', 'The DEFAULT block body');
    $this->assertSaveLayout();

    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The DEFAULT block body');
    $this->drupalGet('node/2');
    $assert_session->pageTextContains('The DEFAULT block body');

    // Enable overrides.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm(['layout[allow_custom]' => TRUE], 'Save');
    $this->drupalGet('node/1/layout');

    // Confirm the block can be edited.
    $this->drupalGet('node/1/layout');
    $this->configureInlineBlock('The DEFAULT block body', 'The NEW block body!');
    $this->assertSaveLayout();
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The NEW block body');
    $assert_session->pageTextNotContains('The DEFAULT block body');
    $this->drupalGet('node/2');
    // Node 2 should use default layout.
    $assert_session->pageTextContains('The DEFAULT block body');
    $assert_session->pageTextNotContains('The NEW block body');

    // Add a basic block with the body field set.
    $this->drupalGet('node/1/layout');
    $this->addInlineBlockToLayout('2nd Block title', 'The 2nd block body');
    $this->assertSaveLayout();
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The NEW block body!');
    $assert_session->pageTextContains('The 2nd block body');
    $this->drupalGet('node/2');
    // Node 2 should use default layout.
    $assert_session->pageTextContains('The DEFAULT block body');
    $assert_session->pageTextNotContains('The NEW block body');
    $assert_session->pageTextNotContains('The 2nd block body');

    // Confirm the block can be edited.
    $this->drupalGet('node/1/layout');
    /** @var \Behat\Mink\Element\NodeElement $inline_block_2 */
    $inline_block_2 = $page->findAll('css', static::INLINE_BLOCK_LOCATOR)[1];
    $uuid = $inline_block_2->getAttribute('data-layout-block-uuid');
    $block_css_locator = static::INLINE_BLOCK_LOCATOR . "[data-layout-block-uuid=\"$uuid\"]";
    $this->configureInlineBlock('The 2nd block body', 'The 2nd NEW block body!', $block_css_locator);
    $this->assertSaveLayout();
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The NEW block body!');
    $assert_session->pageTextContains('The 2nd NEW block body!');
    $this->drupalGet('node/2');
    // Node 2 should use default layout.
    $assert_session->pageTextContains('The DEFAULT block body');
    $assert_session->pageTextNotContains('The NEW block body!');
    $assert_session->pageTextNotContains('The 2nd NEW block body!');

    // The default layout entity block should be changed.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default/layout');
    $assert_session->pageTextContains('The DEFAULT block body');
    // Confirm default layout still only has 1 entity block.
    $assert_session->elementsCount('css', static::INLINE_BLOCK_LOCATOR, 1);
  }

  /**
   * Tests adding a new entity block and then not saving the layout.
   *
   * @dataProvider layoutNoSaveProvider
   */
  public function testNoLayoutSave($operation, $no_save_button_text, $confirm_button_text): void {
    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
      'create and edit custom blocks',
    ]));
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->assertEmpty($this->blockStorage->loadMultiple(), 'No entity blocks exist');
    // Enable layout builder and overrides.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm([
      'layout[enabled]' => TRUE,
      'layout[allow_custom]' => TRUE,
    ], 'Save');

    $this->drupalGet('node/1/layout');
    $this->addInlineBlockToLayout('Block title', 'The block body');
    $page->pressButton($no_save_button_text);
    if ($confirm_button_text) {
      $page->pressButton($confirm_button_text);
    }
    $this->drupalGet('node/1');
    $this->assertEmpty($this->blockStorage->loadMultiple(), 'No entity blocks were created when layout changes are discarded.');
    $assert_session->pageTextNotContains('The block body');

    $this->drupalGet('node/1/layout');

    $this->addInlineBlockToLayout('Block title', 'The block body');
    $this->assertSaveLayout();
    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The block body');
    $blocks = $this->blockStorage->loadMultiple();
    $this->assertCount(1, $blocks);
    /** @var \Drupal\Core\Entity\ContentEntityBase $block */
    $block = array_pop($blocks);
    $revision_id = $block->getRevisionId();

    // Confirm the block can be edited.
    $this->drupalGet('node/1/layout');
    $this->configureInlineBlock('The block body', 'The block updated body');

    $page->pressButton($no_save_button_text);
    if ($confirm_button_text) {
      $page->pressButton($confirm_button_text);
    }
    $this->drupalGet('node/1');

    $blocks = $this->blockStorage->loadMultiple();
    // When reverting or discarding the update block should not be on the page.
    $assert_session->pageTextNotContains('The block updated body');
    if ($operation === 'discard_changes') {
      // When discarding the original block body should appear.
      $assert_session->pageTextContains('The block body');

      $this->assertCount(1, $blocks);
      $block = array_pop($blocks);
      $this->assertEquals($block->getRevisionId(), $revision_id);
      $this->assertEquals('The block body', $block->get('body')->getValue()[0]['value']);
    }
    else {
      // The block should not be visible.
      // Blocks are currently only deleted when the parent entity is deleted.
      $assert_session->pageTextNotContains('The block body');
    }
  }

  /**
   * Provides test data for ::testNoLayoutSave().
   */
  public static function layoutNoSaveProvider() {
    return [
      'discard_changes' => [
        'discard_changes',
        'Discard changes',
        'Confirm',
      ],
      'revert' => [
        'revert',
        'Revert to defaults',
        'Revert',
      ],
    ];
  }

  /**
   * Gets the latest block entity id.
   */
  protected function getLatestBlockEntityId() {
    $block_ids = \Drupal::entityQuery('block_content')
      ->accessCheck(FALSE)
      ->sort('id', 'DESC')
      ->range(0, 1)
      ->execute();
    $block_id = array_pop($block_ids);
    $this->assertNotEmpty($this->blockStorage->load($block_id));
    return $block_id;
  }

}
