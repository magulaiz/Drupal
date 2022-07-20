<?php

namespace Drupal\Tests\Core\Render\Element;

use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Element\DynamicOptions;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Render\Element\DynamicOptions
 * @group Render
 */
class DynamicOptionsTest extends KernelTestBase {

  /**
   * @covers ::processDynamicOptions
   *
   * @dataProvider providerTestProcessDynamicOptions
   */
  public function testProcessDynamicOptions($threshold, $multiple, $expected_element) {
    $form_state = new FormState();

    $element = [
      '#id' => 'test',
      '#field_suffix' => 'test_suffix',
      '#field_prefix' => 'test_prefix',
      '#type' => '#dynamic_options',
      '#options' => range(1, 10),
      '#select_threshold' => $threshold,
      '#multiple' => $multiple,
      '#required' => FALSE,
      // The process function requires these to be set. During regular form
      // building they are always set.
      '#name' => 'test_machine_name',
      '#default_value' => NULL,
      '#parents' => [],
      '#attributes' => [],
      '#value' => NULL,
    ];

    $complete_form = [
      'test_source' => [
        '#type' => 'textfield',
        '#id' => 'source',
      ],
      'test_machine_name' => $element,
    ];

    $form_state->setCompleteForm($complete_form);

    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.element_info');
    $info = $manager->getInfo($expected_element);

    $element = DynamicOptions::processDynamicOptions($element, $form_state, $complete_form);

    $this->assertEquals($expected_element, $element['#type']);
    foreach (array_keys($info) as $key) {
      // Skip #process because it's explicity different in dynamic_options.
      // Skip #options & #multiple because are initialized in the info array and
      // they are overridden.
      if ($key == '#process' || $key == '#options' || $key == '#multiple') {
        continue;
      }
      $this->assertEquals($manager->getInfoProperty($expected_element, $key), $element[$key], $key);
    }
  }

  /**
   * Data provider for testProcessDynamicOptions().
   */
  public function providerTestProcessDynamicOptions() {
    $data = [];
    // Single and below threshold, behave as radios.
    $data[] = [
      12,
      FALSE,
      'radios',
    ];
    // Multiple and below threshold, behave as checkboxes.
    $data[] = [
      12,
      TRUE,
      'checkboxes',
    ];
    // Single and above threshold, behave as single choice select.
    $data[] = [
      7,
      FALSE,
      'select',
    ];
    // Multiple and above threshold, behave as multiple choice select.
    $data[] = [
      7,
      TRUE,
      'select',
    ];

    return $data;
  }

}
