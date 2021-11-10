<?php

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\contextual\FunctionalJavascript\ContextualLinkClickTrait;

/**
 * Tests moving blocks via the form.
 *
 * @group layout_builder
 */
class MoveBlockFormTest extends WebDriverTestBase {

  use ContextualLinkClickTrait;
  use MoveBlocksTrait;

  /**
   * Path prefix for the field UI for the test bundle.
   *
   * @var string
   */
  const FIELD_UI_PREFIX = 'admin/structure/types/manage/bundle_with_section_field';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'block',
    'node',
    'contextual',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->createContentType(['type' => 'bundle_with_section_field']);

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'administer node display',
      'administer node fields',
      'access contextual links',
    ]));

    // Enable layout builder.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm(['layout[enabled]' => TRUE], 'Save');
    $page->clickLink('Manage layout');
    $assert_session->addressEquals(static::FIELD_UI_PREFIX . '/display/default/layout');

    $expected_block_order = [
      '.block-extra-field-blocknodebundle-with-section-fieldlinks',
      '.block-field-blocknodebundle-with-section-fieldbody',
    ];
    $this->assertRegionBlocksOrder(0, 'content', $expected_block_order);

    // Add a top section using the Two column layout.
    $page->clickLink('Add section');
    $assert_session->waitForElementVisible('css', '#drupal-off-canvas');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Two column');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', 'input[value="Add section"]'));
    $page->pressButton('Add section');
    $assert_session->assertNoElementAfterWait('css', '#drupal-off-canvas');
    $this->assertRegionBlocksOrder(1, 'content', $expected_block_order);

    // Add a 'Powered by Drupal' block in the 'first' region of the new section.
    $first_region_block_locator = '[data-layout-delta="0"].layout--twocol-section [data-region="first"] [data-layout-block-uuid]';
    $assert_session->elementNotExists('css', $first_region_block_locator);
    $assert_session->elementExists('css', '[data-layout-delta="0"].layout--twocol-section [data-region="first"] .layout-builder__add-block')->click();
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#drupal-off-canvas a:contains("Powered by Drupal")'));
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Powered by Drupal');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', 'input[value="Add block"]'));
    $assert_session->assertWaitOnAjaxRequest();
    $page->pressButton('Add block');
    $assert_session->assertNoElementAfterWait('css', '#drupal-off-canvas');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', $first_region_block_locator));

    // Ensure the request has completed before the test starts.
    $assert_session->assertWaitOnAjaxRequest();
  }

  /**
   * Tests moving a block.
   */
  public function testMoveBlock() {
    $page = $this->getSession()->getPage();

    // Reorder body field in current region.
    $this->openBodyMoveForm(1, 'content', ['Links', 'Body (current)']);
    $this->moveBlockWithKeyboard('up', 'Body (current)', ['Body (current)*', 'Links']);
    $page->pressButton('Move');
    $this->assertSession()->assertNoElementAfterWait('css', '#drupal-off-canvas');
    $expected_block_order = [
      '.block-field-blocknodebundle-with-section-fieldbody',
      '.block-extra-field-blocknodebundle-with-section-fieldlinks',
    ];
    $this->assertRegionBlocksOrder(1, 'content', $expected_block_order);
    $page->pressButton('Save layout');
    $page->clickLink('Manage layout');
    $this->assertRegionBlocksOrder(1, 'content', $expected_block_order);

    // Move the body block into the first region above existing block.
    $this->openBodyMoveForm(1, 'content', ['Body (current)', 'Links']);
    $page->selectFieldOption('Region', '0:first');
    $this->assertBlockTable(['Powered by Drupal', 'Body (current)']);
    $this->moveBlockWithKeyboard('up', 'Body', ['Body (current)*', 'Powered by Drupal']);
    $page->pressButton('Move');
    $this->assertSession()->assertNoElementAfterWait('css', '#drupal-off-canvas');
    $expected_block_order = [
      '.block-field-blocknodebundle-with-section-fieldbody',
      '.block-system-powered-by-block',
    ];
    $this->assertRegionBlocksOrder(0, 'first', $expected_block_order);

    // Ensure the body block is no longer in the content region.
    $this->assertRegionBlocksOrder(1, 'content', ['.block-extra-field-blocknodebundle-with-section-fieldlinks']);
    $page->pressButton('Save layout');
    $page->clickLink('Manage layout');
    $this->assertRegionBlocksOrder(0, 'first', $expected_block_order);

    // Move into the second region that has no existing blocks.
    $this->openBodyMoveForm(0, 'first', ['Body (current)', 'Powered by Drupal']);
    $page->selectFieldOption('Region', '0:second');
    $this->assertBlockTable(['Body (current)']);
    $page->pressButton('Move');
    $this->assertSession()->assertNoElementAfterWait('css', '#drupal-off-canvas');
    $this->assertRegionBlocksOrder(0, 'second', ['.block-field-blocknodebundle-with-section-fieldbody']);
  }

}
