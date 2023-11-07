<?php

namespace Drupal\Tests\field_ui\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the display modes.
 *
 * @group field_ui
 */
class DisplayModesTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['block', 'field_ui', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a node type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $this->drupalPlaceBlock('local_actions_block');
    $user = $this->drupalCreateUser([
      'administer display modes',
      'administer node display',
      'administer node form display',
    ]);

    $this->drupalLogin($user);
  }

  /**
   * Tests the display modes links in respective tabs.
   *
   * @param string $display_mode
   *   View or Form display mode.
   * @param string $path
   *   Display mode path.
   *
   * @dataProvider providerDisplayModeLinks
   */
  public function testDisplayModeLinks($display_mode, $path) {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->drupalGet("/admin/structure/types/manage/article/$path");
    $assert_session->pageTextContains('Enable view modes');
    $page->clickLink("Add new $display_mode mode");

    // Article checkbox should be checked by default as the form is opened from
    // article content type.
    $checkbox = $page->find('css', '[data-drupal-selector="edit-bundles-by-entity-article"]');
    $this->assertTrue($checkbox->isChecked());

    $edit = [
      'id' => $display_mode,
      'label' => "test-$display_mode",
    ];
    $this->submitForm($edit, 'Save');
    $assert_session->pageTextContains("Saved the test-$display_mode $display_mode mode.");
  }

  /**
   * Data provider for testDisplayModeLinks().
   */
  public function providerDisplayModeLinks() {
    return [
      'view display' => ['view', 'display'],
      'form display' => ['form', 'form-display'],
    ];
  }

}
