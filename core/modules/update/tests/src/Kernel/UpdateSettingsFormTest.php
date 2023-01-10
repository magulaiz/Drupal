<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\update\UpdateSettingsForm;

/**
 * Tests the update manager's settings form.
 *
 * @group update
 *
 * @covers \Drupal\update\UpdateSettingsForm
 */
class UpdateSettingsFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'update', 'user'];

  public function providerFormData() {
    return [
      // Value, expected, form element type.
      [0, 1, 'radios'],
      [1, 1, 'radios'],
      [2, 2, 'number'],
      [6, 6, 'number'],
      [7, 7, 'radios'],
      [8, 8, 'number'],
    ];
  }

  /**
   * @dataProvider providerFormData
   */
  public function testUpdateSettingsForm($config_value, $expected, $form_element_type) {
    // Set config.
    $config = $this->config('update.settings');
    $config->set('check.interval_days', $config_value)->save();
    $this->assertEquals($config_value, $config->get('check.interval_days'));
    // Build a form.
    $container = $this->container;
    $form = new UpdateSettingsForm(
      $container->get('config.factory'),
      $container->get('email.validator')
    );
    // Build the form, assert against form elements.
    $form_elements = $form->buildForm([], new FormState());
    $this->assertSame(
      $form_element_type,
      $form_elements['update_check_frequency']['#type'] ?? FALSE
    );
    $this->assertSame(
      $expected,
      $form_elements['update_check_frequency']['#default_value'] ?? FALSE
    );
  }

}
