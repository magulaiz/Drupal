<?php

namespace Drupal\Tests\Core\Datetime\Element;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\EntityViewTrait;

/**
 * @coversDefaultClass \Drupal\Core\Datetime\Element\Datetime
 * @group Datetime
 */
class DatetimeFormElementTest extends EntityKernelTestBase implements FormInterface {

  use EntityViewTrait;

  /**
   * Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'datetime',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('date_format');
    $this->installConfig(['system']);
    $this->formBuilder = $this->container->get('form_builder');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datetime_form_element_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Test datetime-local element.
    $form['datetime_local_picker'] = [
      '#type' => 'datetime',
      '#date_date_element' => 'datetime-local',
      '#date_time_element' => 'none',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @covers ::valueCallback
   */
  public function testDatetimeLocalNoExceptionMetOnSubmit() {
    $form_state = new FormState();
    $form_state->setValue('datetime_local_picker', ['date' => '2025-02-18T12:00']);
    $this->formBuilder->submitForm($this, $form_state);
    $this->assertEmpty($form_state->getErrors());
  }

  /**
   * @covers ::valueCallback
   */
  public function testDatetimeLocalValueCallback() {
    $element = [
      '#type' => 'datetime',
      '#date_date_element' => 'datetime-local',
      '#date_time_element' => 'none',
    ];
    $input = [
      'date' => '2025-02-18T12:00',
    ];
    $form_state = new FormState();
    $form_state->setValue('datetime_local_picker', ['date' => '2025-02-18T12:00']);

    $result = Datetime::valueCallback($element, $input, $form_state);
    $this->assertIsArray($result);
    $this->assertArrayHasKey('date', $result);
    $this->assertEquals('2025-02-18', $result['date']);
    $this->assertArrayHasKey('time', $result);
    $this->assertEquals('12:00:00', $result['time']);
    $this->assertArrayHasKey('object', $result);
    $this->assertNotEmpty($result['object']);
    $this->assertInstanceOf(DrupalDateTime::class, $result['object']);
  }

  /**
   * @covers ::processDatetime
   */
  public function testDatetimeLocalProcessDatetime() {
    $form = [
      'datetime_local_picker' => [
        '#type' => 'datetime',
        '#date_date_element' => 'datetime-local',
        '#date_date_format' => 'Y-m-d',
        '#date_time_element' => 'none',
        '#date_time_format' => 'H:i:s',
        '#value' => [
          'object' => new DrupalDateTime('2025-02-18T12:00'),
          'date' => '2025-02-18',
          'time' => '12:00:00',
        ],
        '#date_year_range' => '1900:2050',
        '#attributes' => [],
        '#required' => TRUE,
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => 'Submit',
      ]
    ];
    $element = $form['datetime_local_picker'];
    $form_state = new FormState();
    $form_state->setValue('datetime_local_picker', ['date' => '2025-02-18T12:00']);
    $result = Datetime::processDatetime($element, $form_state, $form);
    $this->assertIsArray($result);
    $this->assertArrayHasKey('date', $result);
    $this->assertIsArray($result['date']);
    $this->assertArrayHasKey('#error_no_message', $result['date']);
    $this->assertTrue($result['date']['#error_no_message']);
    $this->assertArrayHasKey('#attributes', $result['date']);
    $this->assertArrayHasKey('min', $result['date']['#attributes']);
    $this->assertEquals('1900-01-01T00:00:00', $result['date']['#attributes']['min']);
    $this->assertArrayHasKey('max', $result['date']['#attributes']);
    $this->assertEquals('2050-12-31T23:59:59', $result['date']['#attributes']['max']);
    $this->assertArrayNotHasKey('time', $result);
  }

}
