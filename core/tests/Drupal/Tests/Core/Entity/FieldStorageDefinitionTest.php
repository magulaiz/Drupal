<?php

namespace Drupal\Tests\Core\Entity;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Unit test for the FieldStorageDefinition class.
 *
 * @group Entity
 * @group field
 * @coversDefaultClass \Drupal\Core\Field\FieldStorageDefinition
 */
class FieldStorageDefinitionTest extends UnitTestCase implements OptionsProviderInterface {

  /**
   * A dummy field type name.
   *
   * @var string
   */
  protected $fieldType;

  /**
   * A test field item returned by the field type manager.
   *
   * @var \Drupal\Core\Field\FieldItemInterface
   */
  protected $testFieldItem;

  /**
   * A dummy field type definition.
   *
   * @var array
   */
  protected $fieldTypeDefinition;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->fieldType = $this->randomMachineName();
    $this->fieldTypeDefinition = [
      'id' => $this->fieldType,
      'field_settings' => [],
      'storage_settings' => [
        'some_storage_setting' => 'some_value',
      ],
      'class' => static::class,
    ];

    $this->testFieldItem = $this->prophesize(FieldItemInterface::class)->reveal();

    $field_type_manager = $this->prophesize(FieldTypePluginManagerInterface::class);
    $field_type_manager->getDefinitions()->willReturn([$this->fieldType => $this->fieldTypeDefinition]);
    $field_type_manager->getDefinition($this->fieldType)->willReturn($this->fieldTypeDefinition);
    $field_type_manager->getDefaultFieldSettings($this->fieldType)->willReturn($this->fieldTypeDefinition['field_settings']);
    $field_type_manager->getDefaultStorageSettings($this->fieldType)->willReturn($this->fieldTypeDefinition['storage_settings']);
    $field_type_manager->createFieldItem(Argument::any(), 0)->willReturn($this->testFieldItem);
    $field_type_manager->getPluginClass(Argument::any())->willReturn(static::class);

    $typed_data_manager = $this->prophesize(TypedDataManager::class);
    $typed_data_manager->getDefaultConstraints(Argument::any())->willReturn([]);
    $typed_data_manager->getDefinition("field_item:{$this->fieldType}")->willReturn(['class' => static::class]);

    $container = new ContainerBuilder();
    $container->set('plugin.manager.field.field_type', $field_type_manager->reveal());
    $container->set('typed_data_manager', $typed_data_manager->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::setName
   * @covers ::getName
   */
  public function testSetName() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setName('foo');
    $this->assertEquals('foo', $definition->getName());
  }

  /**
   * @covers ::getType
   */
  public function testGetType() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals($this->fieldType, $definition->getType());
  }

  /**
   * @covers ::isTranslatable
   * @covers ::setTranslatable
   */
  public function testIsTranslatable() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertFalse($definition->isTranslatable());
    $definition->setTranslatable(TRUE);
    $this->assertTrue($definition->isTranslatable());
  }

  /**
   * @covers ::setCardinality
   * @covers ::getCardinality
   * @covers ::isMultiple
   */
  public function testCardinality() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals(1, $definition->getCardinality());
    $this->assertFalse($definition->isMultiple());

    $definition->setCardinality(5);
    $this->assertEquals(5, $definition->getCardinality());
    $this->assertTrue($definition->isMultiple());

    $definition->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $this->assertEquals(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED, $definition->getCardinality());
    $this->assertTrue($definition->isMultiple());
  }

  /**
   * @covers ::isRevisionable
   */
  public function testIsRevisionable() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertFalse($definition->isRevisionable());

    $definition->setRevisionable(TRUE);
    $this->assertTrue($definition->isRevisionable());

    $definition->setRevisionable(FALSE);
    $this->assertFalse($definition->isRevisionable());

    $definition->setCardinality(2);
    $this->assertTrue($definition->isRevisionable());
  }

  /**
   * @covers ::isQueryable
   * @group legacy
   * @expectedDeprecation FieldStorageDefinitionInterface::isQueryable() is deprecated in Drupal 10.1.0 and will be removed before Drupal 11.0.0. Instead, you should use ::hasCustomStorage(). See https://www.drupal.org/node/2856563.
   */
  public function testIsQueryable() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setCustomStorage(TRUE);
    $this->assertFalse($definition->isQueryable());

    $definition->setCustomStorage(FALSE);
    $this->assertTrue($definition->isQueryable());
  }

  /**
   * @covers ::hasCustomStorage
   */
  public function testHasCustomStorage() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertFalse($definition->hasCustomStorage());

    $definition->setCustomStorage(TRUE);
    $this->assertTrue($definition->hasCustomStorage());

    $definition->setCustomStorage(FALSE);
    $this->assertFalse($definition->hasCustomStorage());
  }

  /**
   * @covers ::hasCustomStorage
   */
  public function testHasCustomStorageException() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setComputed(TRUE);

    $this->expectExceptionObject(new \LogicException('Entity storage cannot store a computed field.'));
    $definition->setCustomStorage(FALSE);
  }

  /**
   * @covers ::isBaseField
   */
  public function testIsBaseField() {
    $definition = FieldStorageDefinition::create($this->fieldType);

    $definition->setBaseField(TRUE);
    $this->assertTrue($definition->isBaseField());

    $definition->setBaseField(FALSE);
    $this->assertFalse($definition->isBaseField());
  }

  /**
   * @covers ::getUniqueStorageIdentifier
   */
  public function testGetUniqueStorageIdentifier() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setTargetEntityTypeId('foo');
    $definition->setName('bar');
    $this->assertEquals('foo-bar', $definition->getUniqueStorageIdentifier());
  }

  /**
   * @covers ::getUniqueStorageIdentifier
   */
  public function testGetTargetEntityTypeId() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setTargetEntityTypeId('foo');
    $this->assertEquals('foo', $definition->getTargetEntityTypeId());
  }

  /**
   * @covers ::isDeleted
   */
  public function testIsDeleted() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertFalse($definition->isDeleted());

    $definition->setDeleted(TRUE);
    $this->assertTrue($definition->isDeleted());

    $definition->setDeleted(FALSE);
    $this->assertFalse($definition->isDeleted());
  }

  /**
   * @covers ::isStorageRequired
   */
  public function testIsStorageRequired() {
    $definition = FieldStorageDefinition::create($this->fieldType);

    // Default to the isRequired status.
    $definition->setRequired(TRUE);
    $this->assertTrue($definition->isStorageRequired());

    $definition->setRequired(FALSE);
    $this->assertFalse($definition->isStorageRequired());

    // Always prioritize the explicit storage required flag.
    $definition->setStorageRequired(TRUE);
    $definition->setRequired(FALSE);
    $this->assertTrue($definition->isStorageRequired());

    $definition->setStorageRequired(FALSE);
    $definition->setRequired(TRUE);
    $this->assertFalse($definition->isStorageRequired());
  }

  /**
   * @covers ::getSchema
   */
  public function testGetSchema() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals([
      'columns' => [
        'foo' => ['foo_column'],
        'bar' => ['bar_column'],
      ],
      'unique keys' => [],
      'indexes' => [],
      'foreign keys' => [],
    ], $definition->getSchema());
  }

  /**
   * A test implementation of \Drupal\Core\Field\FieldItemInterface::schema.
   *
   * @return array
   *   An array of field schema.
   */
  public static function schema() {
    return [
      'columns' => [
        'foo' => ['foo_column'],
        'bar' => ['bar_column'],
      ],
    ];
  }

  /**
   * @covers ::getColumns
   */
  public function testGetColumns() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals([
      'foo' => ['foo_column'],
      'bar' => ['bar_column'],
    ], $definition->getColumns());
  }

  /**
   * @covers ::getProvider
   */
  public function testGetProvider() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $definition->setProvider('foo');
    $this->assertEquals('foo', $definition->getProvider());
  }

  /**
   * Test implementation of \Drupal\Core\Field\FieldItemInterface::propertyDefinitions.
   *
   * @return array
   *   An array of property definitions.
   */
  public static function propertyDefinitions() {
    return [
      'foo' => DataDefinition::create('string')
        ->setLabel('Foo string data')
        ->setRequired(TRUE),
      'bar' => DataDefinition::create('string')
        ->setLabel('Bar string data')
        ->setRequired(TRUE),
    ];
  }

  /**
   * Test implementation of \Drupal\Core\Field\FieldItemInterface::mainPropertyName.
   *
   * @return string
   *   The main property name.
   */
  public static function mainPropertyName() {
    return 'foo';
  }

  /**
   * @covers ::getPropertyDefinition
   */
  public function testGetPropertyDefinition() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $mock_properties = static::propertyDefinitions();
    $this->assertEquals($mock_properties['foo'], $definition->getPropertyDefinition('foo'));
  }

  /**
   * @covers ::getPropertyDefinitions
   */
  public function testGetPropertyDefinitions() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $mock_properties = static::propertyDefinitions();
    $this->assertEquals($mock_properties, $definition->getPropertyDefinitions());
  }

  /**
   * @covers ::getPropertyNames
   */
  public function testGetPropertyNames() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals(['foo', 'bar'], $definition->getPropertyNames());
  }

  /**
   * @covers ::getMainPropertyName
   */
  public function testGetMainPropertyName() {
    $definition = FieldStorageDefinition::create($this->fieldType);
    $this->assertEquals('foo', $definition->getMainPropertyName());
  }

  /**
   * @covers ::setSetting
   * @covers ::setSettings
   * @covers ::getSettings
   * @covers ::getSetting
   */
  public function testSettings() {
    $definition = FieldStorageDefinition::create($this->fieldType);

    // Test defaults are correctly merged from the default storage setting of
    // the field type.
    $this->assertEquals([
      'some_storage_setting' => 'some_value',
    ], $definition->getSettings());
    $this->assertEquals('some_value', $definition->getSetting('some_storage_setting'));

    // New values will not override the existing ones.
    $definition->setSettings(['new_setting' => 'new_value']);
    $this->assertEquals([
      'some_storage_setting' => 'some_value',
      'new_setting' => 'new_value',
    ], $definition->getSettings());
    $this->assertEquals('new_value', $definition->getSetting('new_setting'));

    $definition->setSetting('final_setting', 'final_value');
    $this->assertEquals([
      'some_storage_setting' => 'some_value',
      'new_setting' => 'new_value',
      'final_setting' => 'final_value',
    ], $definition->getSettings());
    $this->assertEquals('final_value', $definition->getSetting('final_setting'));
  }

  /**
   * @covers ::getOptionsProvider
   */
  public function testGetOptionsProvider() {
    $definition = FieldStorageDefinition::create($this->fieldType)->setName('foo');

    $field_item_list = $this->prophesize(FieldItemListInterface::class);

    $entity = $this->prophesize(FieldableEntityInterface::class);
    $entity->get('foo')->willReturn($field_item_list->reveal());

    // The options provider will return an instance of a FieldItem class if it
    // implements OptionsProviderInterface. Since this test class implements
    // the interface, the test field item will be returned and the options
    // provider will match the mock return value of the field type manager.
    $options_provider = $definition->getOptionsProvider('foo', $entity->reveal());
    $this->assertEquals($this->testFieldItem, $options_provider);
    $this->assertNotEquals($field_item_list, $options_provider);
  }

  /**
   * {@inheritdoc}
   *
   * A test implementation for OptionsProviderInterface.
   */
  public function getPossibleValues(AccountInterface $account = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * A test implementation for OptionsProviderInterface.
   */
  public function getPossibleOptions(AccountInterface $account = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * A test implementation for OptionsProviderInterface.
   */
  public function getSettableValues(AccountInterface $account = NULL) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * A test implementation for OptionsProviderInterface.
   */
  public function getSettableOptions(AccountInterface $account = NULL) {
    return NULL;
  }

  /**
   * @covers ::__sleep
   */
  public function testSerialize() {
    $definition = FieldStorageDefinition::create($this->fieldType);

    // Call the only method which instantiates and stores an instances of
    // the typed data manager, to prove the serialization is handled correctly.
    $definition->getConstraints();

    // Override the label of a property definition, to prove the statically
    // cached items are purged when serializing.
    $definition->getPropertyDefinition('foo')->setLabel('Overridden');
    $definition = unserialize(serialize($definition));
    $this->assertEquals('Foo string data', $definition->getPropertyDefinition('foo')->getLabel());
  }

}
