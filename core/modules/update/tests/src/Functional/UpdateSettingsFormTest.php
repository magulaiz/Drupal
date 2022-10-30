<?php

namespace Drupal\Tests\update\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the update manager's settings form.
 *
 * @group update
 */
class UpdateSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests different frequency form elements for different values.
   */
  public function testFrequencyFormElement() {
    $this->drupalLogin(
      $this->createUser(['administer site configuration'])
    );

    // Update frequency settings form element will be radio buttons for config
    // values 1 or 7, a number field for other values.
    $data = [
      // Value, expected, form element.
      [0, 1, 'radio'],
      [1, 1, 'radio'],
      [2, 2, 'number'],
      [6, 6, 'number'],
      [7, 7, 'radio'],
      [8, 8, 'number'],
    ];

    foreach ($data as [$value, $expected, $form_element_type]) {
      $config = $this->config('update.settings');
      $config->set('check.interval_days', $value);
      $config->save();
      $this->assertEquals($value, $config->get('check.interval_days'));

      $this->drupalGet('admin/reports/updates/settings');
      $assert = $this->assertSession();
      $assert->statusCodeEquals(200);

      if ($form_element_type == 'radio') {
        // Radio buttons have the value in the name.
        $field = $this->getSession()->getPage()->findField('edit-update-check-frequency-' . $expected);
        $this->assertEquals($form_element_type, $field->getAttribute('type'));
        // Only the current value's radio button will be checked.
        $this->assertEquals('checked', $field->getAttribute('checked'));
      }
      else {
        $field = $this->getSession()->getPage()->findField('update_check_frequency');
        $this->assertEquals($form_element_type, $field->getAttribute('type'));
        $this->assertEquals($expected, $field->getValue());
      }
    }
  }

}
