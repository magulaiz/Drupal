<?php

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

/**
 * Trait for moving blocks in the Layout Builder UI.
 */
trait MoveBlocksTrait {

  /**
   * Moves a block in the draggable table.
   *
   * @param string $direction
   *   The direction to move the block in the table.
   * @param string $block_label
   *   The block label.
   * @param array $updated_blocks
   *   The updated blocks order.
   */
  protected function moveBlockWithKeyboard($direction, $block_label, array $updated_blocks) {
    $keys = [
      'up' => 38,
      'down' => 40,
    ];
    $key = $keys[$direction];
    $handle = $this->findRowHandle($block_label);

    $handle->keyDown($key);
    $handle->keyUp($key);

    $handle->blur();
    $this->assertBlockTable($updated_blocks);
  }

  /**
   * Finds the row handle for a block in the draggable table.
   *
   * @param string $block_label
   *   The block label.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The row handle element.
   */
  protected function findRowHandle($block_label) {
    $assert_session = $this->assertSession();
    return $assert_session->elementExists('css', "[data-drupal-selector=\"edit-components\"] td:contains(\"$block_label\") a.tabledrag-handle");
  }

  /**
   * Asserts that blocks are in the correct order for a region.
   *
   * @param int $section_delta
   *   The section delta.
   * @param string $region
   *   The region.
   * @param array $expected_block_selectors
   *   The block selectors.
   */
  protected function assertRegionBlocksOrder($section_delta, $region, array $expected_block_selectors) {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $region_selector = "[data-layout-delta=\"$section_delta\"] [data-region=\"$region\"]";

    // Get all blocks currently in the region.
    $blocks = $page->findAll('css', "$region_selector [data-layout-block-uuid]");
    $this->assertCount(count($expected_block_selectors), $blocks);

    /** @var \Behat\Mink\Element\NodeElement $block */
    foreach ($blocks as $block) {
      $block_selector = array_shift($expected_block_selectors);
      $assert_session->elementsCount('css', "$region_selector $block_selector", 1);
      $expected_block = $page->find('css', "$region_selector $block_selector");
      $this->assertSame($expected_block->getAttribute('data-layout-block-uuid'), $block->getAttribute('data-layout-block-uuid'));
    }
  }

  /**
   * Open block for the body field.
   *
   * @param int $delta
   *   The section delta where the field should be.
   * @param string $region
   *   The region where the field should be.
   * @param array $initial_blocks
   *   The initial blocks that should be shown in the draggable table.
   */
  protected function openBodyMoveForm($delta, $region, array $initial_blocks) {
    $assert_session = $this->assertSession();

    $body_field_locator = "[data-layout-delta=\"$delta\"] [data-region=\"$region\"] .block-field-blocknodebundle-with-section-fieldbody";
    $this->clickContextualLink($body_field_locator, 'Move');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertNotEmpty($assert_session->waitForElementVisible('named', ['select', 'Region']));
    $assert_session->fieldValueEquals('Region', "$delta:$region");
    $this->assertBlockTable($initial_blocks);
  }

  /**
   * Asserts the correct block labels appear in the draggable tables.
   *
   * @param string[] $expected_block_labels
   *   The expected block labels.
   */
  protected function assertBlockTable(array $expected_block_labels) {
    $page = $this->getSession()->getPage();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $block_tds = $page->findAll('css', '.layout-builder-components-table__block-label');
    $this->assertCount(count($block_tds), $expected_block_labels);
    /** @var \Behat\Mink\Element\NodeElement $block_td */
    foreach ($block_tds as $block_td) {
      $this->assertSame(array_shift($expected_block_labels), trim($block_td->getText()));
    }
  }

}
