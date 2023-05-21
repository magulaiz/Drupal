<?php

namespace Drupal\Tests\field\Kernel\Number;

use Drupal\entity_test\Entity\EntityTestRev;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test wrap feature for formatters number.
 *
 * @group field
 */
class NumberItemFormatterTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'field',
    'entity_test',
    'system',
    'filter',
    'user',
    'field_test',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Configure the theme system.
    $this->installConfig(['system', 'field']);
    $this->installEntitySchema('entity_test_rev');
    $this->installEntitySchema('entity_test_label');
    $this->entityType = 'entity_test_rev';
    $this->bundle = $this->entityType;
  }

  /**
   * Test numeric field formatters.
   *
   * @dataProvider dataNumericFormatterProvider
   *
   * @param $field_type
   *   The field type to be tested.
   * @param $value
   *   The value to set on the field.
   * @param $settings
   *   The array of settings to be used on formatter.
   */
  public function testNumericFormatter($field_type, $value, $settings) {
    FieldStorageConfig::create([
      'entity_type' => $this->entityType,
      'field_name' => 'field_' . $field_type,
      'type' => $field_type,
    ])->save();
    FieldConfig::create([
      'entity_type' => $this->entityType,
      'field_name' => 'field_' . $field_type,
      'bundle' => $this->bundle,
    ])->save();

    $this->display = \Drupal::service('entity_display.repository')
      ->getViewDisplay($this->entityType, $this->bundle)
      ->setComponent('field_' . $field_type, [
        'type' => 'number_' . ($field_type !== 'float' ? $field_type : 'decimal'),
        'settings' => $settings,
      ]);
    $this->display->save();

    $entity = EntityTestRev::create();
    $entity->set('field_' . $field_type, $value);
    $entity->save();
    $content = $this->display->build($entity);
    $content = $this->render($content);
    $needle = sprintf('<%s>field_%s</%s>', $settings['wrap_label_tag'], $field_type, $settings['wrap_label_tag']);
    $this->assertStringContainsString($needle, $content);
  }

  /**
   * Provider function to test integer, float and decimal.
   *
   * @return \Generator
   */
  public function dataNumericFormatterProvider() {
    yield ['integer', 1, ['wrap_label_tag' => 'h2']];
    yield ['float', 1.0, ['wrap_label_tag' => 'h3']];
    yield ['decimal', 1.0, ['wrap_label_tag' => 'h4']];
  }

}
