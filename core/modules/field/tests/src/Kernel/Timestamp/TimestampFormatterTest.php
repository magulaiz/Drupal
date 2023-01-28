<?php

namespace Drupal\Tests\field\Kernel\Timestamp;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the timestamp formatters.
 *
 * @group field
 */
class TimestampFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field',
    'text',
    'entity_test',
    'user',
  ];

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var string
   */
  protected $bundle;

  /**
   * @var string
   */
  protected $fieldName;

  /**
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * The label defined to the field created.
   *
   * @var string
   */
  protected $fieldLabel;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fieldLabel = $this->randomMachineName();
    $this->installConfig(['system']);
    $this->installConfig(['field']);
    $this->installEntitySchema('entity_test');

    $this->entityType = 'entity_test';
    $this->bundle = $this->entityType;
    $this->fieldName = mb_strtolower($this->randomMachineName());

    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityType,
      'type' => 'timestamp',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
      'label' => $this->fieldLabel,
    ]);
    $instance->save();

    $this->display = \Drupal::service('entity_display.repository')
      ->getViewDisplay($this->entityType, $this->bundle)
      ->setComponent($this->fieldName, [
        'type' => 'boolean',
        'settings' => [],
      ]);
    $this->display->save();
  }

  /**
   * Renders fields of a given entity with a given display.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity object with attached fields to render.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display to render the fields in.
   *
   * @return string
   *   The rendered entity fields.
   */
  protected function renderEntityFields(FieldableEntityInterface $entity, EntityViewDisplayInterface $display) {
    $content = $display->build($entity);
    $content = $this->render($content);
    return $content;
  }

  /**
   * Helper function to update component formatter type and settings.
   *
   * @param string $type
   *   The time of formatter.
   * @param array $settings
   *   Array of settings to be set.
   */
  protected function updateComponentSettings($type, $settings) {
    $component = $this->display->getComponent($this->fieldName);
    $component['type'] = $type;
    $component['settings'] = array_merge($component['settings'], $settings);
    $this->display->setComponent($this->fieldName, $component);
  }

  /**
   * Helper function to create entity with value in fieldName.
   *
   * @param mixed $value
   *   The value to be set on fieldName.
   */
  protected function createEntityWithValue($value = NULL) {
    if (empty($value)) {
      $value = \Drupal::time()->getRequestTime();
    }
    $entity = EntityTest::create([]);
    $entity->{$this->fieldName}->value = $value;
    return $entity;
  }

  /**
   * Tests TimestampFormatter.
   */
  public function testTimestampFormatter() {
    $data = [];

    // Test standard formats.
    $date_formats = array_keys(\Drupal::entityTypeManager()->getStorage('date_format')->loadMultiple());

    foreach ($date_formats as $date_format) {
      $data[] = ['date_format' => $date_format, 'custom_date_format' => '', 'timezone' => ''];
    }

    $data[] = ['date_format' => 'custom', 'custom_date_format' => 'r', 'timezone' => ''];
    $data[] = ['date_format' => 'custom', 'custom_date_format' => 'e', 'timezone' => 'Asia/Tokyo'];

    foreach ($data as $settings) {
      [$date_format, $custom_date_format, $timezone] = array_values($settings);
      if (empty($timezone)) {
        $timezone = NULL;
      }
      $value = REQUEST_TIME - 87654321;
      $expected = \Drupal::service('date.formatter')->format($value, $date_format, $custom_date_format, $timezone);
      $this->updateComponentSettings('timestamp', $settings);
      $entity = $this->createEntityWithValue($value);

      $this->renderEntityFields($entity, $this->display);
      $this->assertRaw($expected);
    }
  }

  /**
   * Tests TimestampAgoFormatter.
   */
  public function testTimestampAgoFormatter() {
    $data = [];

    foreach ([1, 2, 3, 4, 5, 6] as $granularity) {
      $data[] = [
        'future_format' => '@interval hence',
        'past_format' => '@interval ago',
        'granularity' => $granularity,
      ];
    }

    foreach ($data as $settings) {
      $future_format = $settings['future_format'];
      $past_format = $settings['past_format'];
      $granularity = $settings['granularity'];
      $request_time = \Drupal::requestStack()->getCurrentRequest()->server->get('REQUEST_TIME');

      // Test a timestamp in the past
      $value = $request_time - 87654321;
      $expected = new FormattableMarkup($past_format, ['@interval' => \Drupal::service('date.formatter')->formatTimeDiffSince($value, ['granularity' => $granularity])]);
      $this->updateComponentSettings('timestamp_ago', $settings);
      $entity = $this->createEntityWithValue($value);
      $this->renderEntityFields($entity, $this->display);
      $this->assertRaw($expected);

      // Test a timestamp in the future
      $value = $request_time + 87654321;
      $expected = new FormattableMarkup($future_format, ['@interval' => \Drupal::service('date.formatter')->formatTimeDiffUntil($value, ['granularity' => $granularity])]);
      $this->updateComponentSettings('timestamp_ago', $settings);
      $entity = $this->createEntityWithValue($value);
      $this->renderEntityFields($entity, $this->display);
      $this->assertRaw($expected);
    }
  }

  /**
   * Test timestamp wrapper label.
   */
  public function testTimestampWrapperLabel() {
    $wrap_label_tag = 'h3';
    $this->updateComponentSettings('timestamp', [
      'wrap_label_tag' => $wrap_label_tag,
    ]);
    $entity = $this->createEntityWithValue();
    $content = $this->renderEntityFields($entity, $this->display);
    $message = sprintf("Label of field %s was not wrapped by tag %s heading.", $this->fieldName, $wrap_label_tag);
    $this->assertStringContainsString('<h3>' . $this->fieldLabel . '</h3>', $content, $message);
  }

  /**
   * Test timestamp ago formatter wrapper label.
   */
  public function testTimestampAgoWrapperLabel() {
    $wrap_label_tag = 'h3';
    $this->updateComponentSettings('timestamp_ago', [
      'wrap_label_tag' => $wrap_label_tag,
    ]);
    $entity = $this->createEntityWithValue();
    $content = $this->renderEntityFields($entity, $this->display);
    $message = sprintf("Label of field %s was not wrapped by tag %s heading.", $this->fieldName, $wrap_label_tag);
    $this->assertStringContainsString('<h3>' . $this->fieldLabel . '</h3>', $content, $message);
  }

}
