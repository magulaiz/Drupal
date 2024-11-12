<?php

declare(strict_types=1);

namespace Drupal\Tests\inline_form_errors\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests unique presence of inline form errors.
 */
class MultipleInlineFormErrorsTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'file',
    'inline_form_errors',
  ];

  /**
   * Provides themes for the test.
   *
   * @return array
   *   An array of themes.
   */
  public static function themeProvider() {
    return [
      ['stark'],
      ['olivero'],
    ];
  }

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
      'name' => 'Page',
      'type' => 'page',
    ]);
    $web_user = $this->drupalCreateUser([
      'create page content',
      'edit own page content',
    ]);
    $this->drupalLogin($web_user);
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_files',
      'entity_type' => 'node',
      'type' => 'file',
      'cardinality' => 5,
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_name' => 'field_files',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Files',
      'required' => TRUE,
    ]);
    $field->save();

    \Drupal::service('entity_display.repository')->getFormDisplay('node', 'page', 'default')
      ->setComponent('field_files', [
        'type' => 'file_generic',
      ])->save();

    \Drupal::service('entity_display.repository')->getViewDisplay('node', 'page', 'default')
      ->setComponent('field_files', [
        'type' => 'file_default',
      ])->save();
  }

  /**
   * Tests number of inline form errors for limited multiple file widget.
   *
   * @dataProvider themeProvider
   */
  public function testInlineFormErrorsCount($theme) {
    \Drupal::service('theme_installer')->install([$theme]);
    $this->config('system.theme')
      ->set('default', $theme)
      ->save();
    $this->drupalGet('node/add/page');
    $this->getSession()->getPage()->fillField('Title', 'Test Node');

    $this->assertSession()->waitForText('Add a new file');

    // Click the save button without uploading any files.
    $this->getSession()->getPage()->pressButton('Save');

    // Wait for the error message to be visible.
    $this->assertSession()->waitForElementVisible('css', '.form-item--error-message');

    // Assert that only one element with the class form-item--error-message exists.
    $elements = $this->getSession()->getPage()->findAll('css', '.form-item--error-message');
    $this->assertCount(1, $elements, 'Only one error message should be present.');
  }

}
