<?php

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the layout overview UI.
 *
 * @group layout_builder
 */
class OverviewUITest extends WebDriverTestBase {

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
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createContentType(['type' => 'bundle_with_section_field']);

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'create and edit custom blocks',
      'administer node display',
      'administer node fields',
      'access contextual links',
    ]));

    // Enable layout builder.
    $this->drupalPostForm(
      static::FIELD_UI_PREFIX . '/display/default',
      ['layout[enabled]' => TRUE],
      'Save'
    );
  }

  /**
   * Perform layout operations via overview dialog.
   */
  public function testOperationsWithOverview() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default/layout');

    $this->openOverview();

    $this->addSection('Two column', [
      'configure_section' => [
        'options' => [
          'layout_settings[column_widths]' => '33-67',
        ],
      ],
    ]);

    $this->addBlock(0, 'first', 'Powered by Drupal', '.block-system-powered-by-block');
    $this->addBlock(0, 'second', 'Changed', '.block-system-powered-by-block');

    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '.layout--twocol-section--33-67'));

    $this->dropbuttonTask(0, 'first', 'system_powered_by_block', 'remove');
    $assert_session->assertNoElementAfterWait('css', '.block-system-powered-by-block');
    $this->dropbuttonTask(1, 'content', 'extra_field_block:node:bundle_with_section_field:links', 'configure', FALSE);
    $page->fillField('settings[label]', 'Gold Star For Robot Boy');
    $page->checkField('settings[label_display]');
    $page->pressButton('Update');

    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
    $this->assertTrue($assert_session->waitForText('Gold Star For Robot Boy'));

    $expected_block_order = [
      '.block-extra-field-blocknodebundle-with-section-fieldlinks',
      '.block-field-blocknodebundle-with-section-fieldbody',
    ];
    $this->assertRegionBlocksOrder(1, 'content', $expected_block_order);
    $this->dropbuttonTask(1, 'content', 'extra_field_block:node:bundle_with_section_field:links', 'move', FALSE);
    $this->moveBlockWithKeyboard('down', 'Gold Star For Robot Boy (current)', ['Body', 'Gold Star For Robot Boy (current)*']);
    $page->pressButton('Move');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
    $expected_block_order = [
      '.block-field-blocknodebundle-with-section-fieldbody',
      '.block-extra-field-blocknodebundle-with-section-fieldlinks',
    ];
    $this->assertRegionBlocksOrder(1, 'content', $expected_block_order);
  }

  /**
   * Opens the overview dialog.
   */
  protected function openOverview() {
    $this->getSession()->getPage()->clickLink('Layout overview');
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
  }

  /**
   * Add a section via the overview dialog.
   *
   * @param string $layout
   *   The layout the section should use.
   * @param array|bool $options
   *   If the layout has options, set to true or an array with fields/values.
   * @param int $delta
   *   Where to add the section.
   */
  protected function addSection($layout, $options = [], $delta = 0) {
    $number_of_sections_before = count($this->getSession()->getPage()->findAll('css', '.layout-builder__section'));
    $add_section_links = $this->getSession()->getPage()->findAll('css', '#drupal-off-canvas #blocks .new-section__link');
    $this->assertCount(2, $add_section_links);
    $add_section_link = $add_section_links[$delta];
    $add_section_link->click();
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('named', ['link', $layout]));

    $this->clickLink($layout);
    if (!empty($options['configure_section'])) {
      $add_section_confirm_button = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas [value="Add section"]');
      $this->assertNotEmpty($add_section_confirm_button);
      if (isset($options['configure_section']['options'])) {
        foreach ($options['configure_section']['options'] as $field => $value) {
          $this->getSession()->getPage()->findField($field)->setValue($value);
        }
      }
      $add_section_confirm_button->click();
    }

    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
    $number_of_sections_after = count($this->getSession()->getPage()->findAll('css', '.layout-builder__section'));
    $this->assertEqual($number_of_sections_after, $number_of_sections_before + 1);

  }

  /**
   * Removes a section via the overview dialog.
   *
   * @param int $delta
   *   The section to remove.
   * @param bool $confirm_remove
   *   Set to FALSE for this to stop at the confirmation dialog.
   */
  protected function removeSection($delta, $confirm_remove = TRUE) {
    $remove_button = $this->getSession()->getPage()->find('css', "[data-section-delta='$delta'] .remove-section");
    $this->assertNotEmpty($remove_button);
    $remove_button->press();
    $this->assertTrue($this->assertSession()->waitForText('Are you sure you want to remove section'));
    if ($confirm_remove) {
      $this->getSession()->getPage()->pressButton('Remove');
      $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas #blocks'));

    }
  }

  /**
   * Add a block via the overview dialog.
   *
   * @param int $delta
   *   The section where the block is added.
   * @param string $region
   *   The region within the section where the block is added.
   * @param string $block_name
   *   The name of the block to add.
   * @param string $locator
   *   Optional - a css locator to assert exists after block is added.
   */
  protected function addBlock($delta, $region, $block_name, $locator = '') {
    $add_button = $this->getSession()->getPage()->find('css', "[data-section-region-delta='$delta|$region'] .add-block a");
    $this->assertNotEmpty($add_button);
    $add_button->press();
    $this->assertTrue($this->assertSession()->waitForText('Choose a block'));
    $this->getSession()->getPage()->clickLink($block_name);
    $this->assertTrue($this->assertSession()->waitForText('Add block'));
    $add_block_confirm_button = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas [value="Add block"]');
    $this->assertNotEmpty($add_block_confirm_button);
    $add_block_confirm_button->press();
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
    if (!empty($locator)) {
      $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', $locator));
    }
  }

  /**
   * Clicks a dropbutton for a block in the overview dialog.
   *
   * @param int $delta
   *   The section where the block is present.
   * @param string $region
   *   The region within the section where the block is present.
   * @param string $plugin_id
   *   The plugin id of the block.
   * @param string $task
   *   The dropbutton task to choose.
   * @param bool $submit_with_defaults
   *   When true, the task will be completed automatically. When false, the
   *   this method will complete with the task's config form open.
   */
  protected function dropbuttonTask($delta, $region, $plugin_id, $task, $submit_with_defaults = TRUE) {
    $block_locator = "[data-section-region-block-delta='$delta|$region|$plugin_id']";
    $dropbutton = $this->getSession()->getPage()->find('css', "$block_locator .dropbutton-toggle button");
    $this->assertNotEmpty($dropbutton);
    $dropbutton->press();
    $open_dropbutton = $this->assertSession()->waitForElementVisible('css', "$block_locator .open");
    $this->assertNotEmpty($open_dropbutton);
    $task_link = $open_dropbutton->find('css', ".$task-block");
    $this->assertNotEmpty($task_link);
    $task_link->click();
    switch ($task) {
      case 'configure':
        $submit_button = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas [value="Update"]');
        break;

      case 'remove':
        $submit_button = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas [value="Remove"]');
        break;

      case 'move':
        $submit_button = $this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas [value="Move"]');
        break;
    }

    // Use instead of assertNotEmpty as variable could potentially not exist.
    $this->assertTrue(!empty($submit_button));

    if ($submit_with_defaults) {
      $submit_button->press();
      $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#drupal-off-canvas #blocks'));
    }
  }

}
