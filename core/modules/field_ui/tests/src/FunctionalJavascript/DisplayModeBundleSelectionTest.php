<?php

declare(strict_types=1);

namespace Drupal\Tests\field_ui\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the bundle selection for view & form display modes.
 *
 * @group field_ui
 */
class DisplayModeBundleSelectionTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType([
      'name' => 'Article',
      'type' => 'article',
    ]);
    $this->drupalCreateContentType([
      'name' => 'Page',
      'type' => 'page',
    ]);
    $this->drupalPlaceBlock('local_actions_block');
    $user = $this->drupalCreateUser([
      'administer display modes',
      'administer node display',
      'administer node form display',
    ]);
    // Create a new form mode 'foobar' for content.
    EntityFormMode::create([
      'id' => 'node.foobar',
      'targetEntityType' => 'node',
      'label' => 'Foobar',
    ])->save();

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

    $page->find('css', '[data-drupal-selector="edit-modes"]')->pressButton('Enable view modes');
    $this->clickLink("Add new $display_mode mode");
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertEquals("Add new Content $display_mode mode", $this->assertSession()->waitForElement('css', '.ui-dialog-title')->getText());
    $this->htmlOutput();

    $assert_session->waitForElementVisible('css', '.ui-dialog');
    $assert_session->assertVisibleInViewport('css', '.ui-dialog .ui-dialog-content');
    $this->htmlOutput();
    $assert_session->elementExists('css', '[name="label"]');
    $this->htmlOutput();
    $page->fillField('label', "Test $display_mode");
    $this->htmlOutput();

    // Article checkbox should be checked by default as the form is opened from
    // article content type.
    $this->assertNotEmpty($assert_session->waitForText('Enable this ' . $display_mode . ' mode for the following Content types'));
    $checkbox = $page->find('css', '[data-drupal-selector="edit-bundles-by-entity-article"]');
    $this->assertTrue($checkbox->isChecked());
    $page->find('css', '.ui-dialog-buttonset')->pressButton('Save');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertNotEmpty($assert_session->waitForText("Saved the Test $display_mode $display_mode mode."));

    // Check that the display mode checkbox is checked.
    $page->find('css', '[data-drupal-selector="edit-modes"]')->pressButton('Enable view modes');
    $this->assertTrue($page->find('css', "#edit-display-modes-custom-test-$display_mode")->isChecked());
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
