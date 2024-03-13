<?php

namespace Drupal\Tests\layout_builder\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

// cspell:ignore fieldslinks

/**
 * Tests how Layout Builder handles changes to entity fields.
 *
 * @group layout_builder
 */
class FieldValuesTest extends WebDriverTestBase {

  /**
   * Path prefix for the field UI for the test bundle.
   *
   * @var string
   */
  const FIELD_UI_PREFIX = 'admin/structure/types/manage/bundle_for_testing_fields';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createContentType(['type' => 'bundle_for_testing_fields']);

    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'configure any layout',
      'administer node display',
      'administer nodes',
      'bypass node access',
    ]));

    // Enable layout builder.
    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm(['layout[enabled]' => TRUE], 'Save');

    $this->createNode([
      'type' => 'bundle_for_testing_fields',
      'body' => [
        [
          'value' => 'The initial value',
        ],
      ],
    ])->save();
  }

  /**
   * Test that changes to fields are visible in layout and UI.
   */
  public function testUiCurrentWithEntityFieldChanges() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet(static::FIELD_UI_PREFIX . '/display/default');
    $this->submitForm(['layout[allow_custom]' => TRUE], 'Save');

    $this->drupalGet('node/1');
    $assert_session->pageTextContains('The initial value');
    $this->drupalGet('node/1/layout');

    // Change the Links block label in the override to confirm that these
    // changes aren't removed when entity field values are updated.
    $links_block = $page->findAll('css', ".layout__region--content > .layout-builder-block");
    // The second block is the body field so that is why $links_block[1].
    $links_block_uuid = $links_block[1]->getAttribute('data-layout-block-uuid');
    $this->drupalGet('layout_builder/update/block/overrides/node.1/0/content/' . $links_block_uuid);
    $page->checkField('settings[label_display]');
    $overridden_label = 'This is a label in the override';
    $page->fillField('settings[label]', $overridden_label);
    $page->pressButton('Update');

    $assert_session->pageTextContains('The initial value');
    $assert_session->pageTextContains($overridden_label);

    $changed_body_value = 'The changed value';

    $this->drupalGet('node/1/edit');
    $this->submitForm(['body[0][value]' => $changed_body_value], 'Save');

    // Confirm that changes to a field are seen in the Layout UI without
    // altering a layout's changes in the tempstore.
    $assert_session->pageTextContains($changed_body_value);
    $assert_session->pageTextNotContains($overridden_label);
    $this->drupalGet('node/1/layout');
    $assert_session->pageTextContains($changed_body_value);
    $assert_session->pageTextContains($overridden_label);

    // Confirm the fields appear correctly after the override is saved and
    // no tempstore is present.
    $page->pressButton('Save layout');
    $assert_session->pageTextContains($changed_body_value);
    $assert_session->pageTextContains($overridden_label);
    $this->drupalGet('node/1/layout');
    $assert_session->pageTextContains($changed_body_value);
    $assert_session->pageTextContains($overridden_label);
  }

}
