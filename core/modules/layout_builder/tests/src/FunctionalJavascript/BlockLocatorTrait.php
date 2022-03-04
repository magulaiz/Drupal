<?php

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

/**
 * Functions for retrieving block-specific selectors.
 */
trait BlockLocatorTrait {

  /**
   * The locator for a layout's block for the body field.
   *
   * @var string
   */
  protected $bodyLocator;

  /**
   * Retrieves a block's UUID based CSS locator from its label.
   *
   * The only way to target a specific block via CSS is the
   * data-layout-content-preview-placeholder-label attribute. However, this
   * locator can not be parsed by some test methods due to nested double quotes
   * in the locator. This takes the locator with nested double quotes and
   * returns an easily parsed UUID based locator.
   *
   * @param string $label
   *   The label of the block.
   *
   * @return string
   *   A CSS selector that matches only that block.
   */
  protected function getLocatorFromPlaceholderLabel($label) {
    $block = $this->assertSession()->waitForElement('css', "[data-layout-content-preview-placeholder-label='$label']");
    $this->assertNotEmpty($block);
    $this->assertTrue($block->hasAttribute('data-layout-block-uuid'));
    $block_uuid = $block->getAttribute('data-layout-block-uuid');
    return "[data-layout-block-uuid=\"$block_uuid\"]";
  }

  /**
   * Returns a UUID based locator for a layout's body field.
   *
   * The body field is the most commonly used in tests, so it gets a dedicated
   * method here.
   *
   * @return string
   *   The locator for a layout's body field.
   */
  protected function getBodyLocator() {
    if (empty($this->bodyLocator)) {
      $this->bodyLocator = $this->getLocatorFromPlaceholderLabel('"Body" field');
    }
    return $this->bodyLocator;
  }

}
