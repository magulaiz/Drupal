<?php

namespace Drupal\Tests\field\Kernel\Number;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test wrap feature for formatters number.
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
    $this->fieldName = mb_strtolower('my_test_field');

    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityType,
      'type' => 'string',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();

    $this->display = \Drupal::service('entity_display.repository')
      ->getViewDisplay($this->entityType, $this->bundle)
      ->setComponent($this->fieldName, [
        'type' => 'string',
        'settings' => [],
      ]);
    $this->display->save();

    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Test formatter wrap label for integer formatter.
   */
  public function testIntegerFormatter() {
    // @todo implement test.
  }

  /**
   * Test formatter wrap label for decimal formatter.
   */
  public function testDecimalFormatter() {
    // @todo implement test.
  }

  /**
   * Test formatter wrap label for float formatter.
   */
  public function testFloatFormatter() {
    // @todo implement test.
  }

}
