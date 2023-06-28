<?php

namespace Drupal\Tests\field_ui\Unit;

use Drupal\field_ui\Form\FieldConfigEditForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\field_ui\Form\FieldConfigEditForm
 *
 * @group field_ui
 */
class FieldConfigEditFormTest extends UnitTestCase {

  /**
   * The field config edit form.
   *
   * @var \Drupal\field_ui\Form\FieldConfigEditForm|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fieldConfigEditForm;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $entity_type_bundle_info = $this->createMock('\Drupal\Core\Entity\EntityTypeBundleInfoInterface');
    $temp_store = $this->createMock('\Drupal\Core\TempStore\PrivateTempStoreFactory');
    $typed_data_manager = $this->createMock('\Drupal\Core\TypedData\TypedDataManagerInterface');
    $entity_display_repository = $this->createMock('\Drupal\Core\Entity\EntityDisplayRepositoryInterface');
    $plugin_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerBase');
    $selection_manager = $this->createMock('\Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface');
    $this->fieldConfigEditForm = new FieldConfigEditForm($entity_type_bundle_info, $temp_store, $typed_data_manager, $entity_display_repository, $plugin_manager, $selection_manager);
  }

  /**
   * @covers ::hasAnyRequired
   *
   * @dataProvider providerRequired
   */
  public function testHasAnyRequired(array $element, bool $result) {
    $reflection = new \ReflectionClass('\Drupal\field_ui\Form\FieldConfigEditForm');
    $method = $reflection->getMethod('hasAnyRequired');
    $method->setAccessible(TRUE);
    $this->assertEquals($result, $method->invoke($this->fieldConfigEditForm, $element));
  }

  /**
   * Provides test cases with required and optional elements.
   */
  public function providerRequired(): \Generator {
    yield 'required' => [
      [['#required' => TRUE]],
      TRUE,
    ];
    yield 'optional' => [
      [['#required' => FALSE]],
      FALSE,
    ];
    yield 'required and optional' => [
      [['#required' => TRUE], ['#required' => FALSE]],
      TRUE,
    ];
    yield 'empty' => [
      [[], []],
      FALSE,
    ];
    yield 'multiple required' => [
      [[['#required' => TRUE]], [['#required' => TRUE]]],
      TRUE,
    ];
    yield 'multiple optional' => [
      [[['#required' => FALSE]], [['#required' => FALSE]]],
      FALSE,
    ];
  }

  /**
   * @covers ::hasAnyElementDefaultValue
   *
   * @dataProvider providerDefaultValue
   */
  public function testHasAnyElementDefaultValueRecursive(array $element, bool $result) {
    $reflection = new \ReflectionClass('\Drupal\field_ui\Form\FieldConfigEditForm');
    $method = $reflection->getMethod('hasAnyElementDefaultValue');
    $method->setAccessible(TRUE);
    $this->assertEquals($result, $method->invoke($this->fieldConfigEditForm, $element));
  }

  /**
   * Provides test cases for detecting default values in a render array.
   */
  public function providerDefaultValue(): \Generator {
    yield 'includes default value' => [
      [['#default_value' => '🐈']],
      TRUE,
    ];
    yield 'no default value' => [
      [[]],
      FALSE,
    ];
    yield 'includes default value deep' => [
      [[[['#default_value' => '🐈']]]],
      TRUE,
    ];
    yield 'includes default value and no default value' => [
      [['#default_value' => '🐈'], []],
      TRUE,
    ];
  }

}
