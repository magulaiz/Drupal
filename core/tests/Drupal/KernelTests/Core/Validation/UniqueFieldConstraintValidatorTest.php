<?php

namespace Drupal\KernelTests\Core\Validation;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests UniqueField validation constraint with both valid and invalid values.
 *
 * @coversDefaultClass \Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator
 * @group Validation
 */
class UniqueFieldConstraintValidatorTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test_constraints',
    'link',
    'telephone',
    'datetime',
    'options',
  ];

  /**
   * The entity type used in this test.
   *
   * @var string
   */
  protected $entityType = 'entity_test_constraints';

  /**
   * An array of field types and their settings.
   *
   * @var array
   */
  protected $fields = [
    'link' => [],
    'boolean' => [],
    'email' => [],
    'telephone' => [],
    'datetime' => [],
    'list_integer' => [
      'allowed_values' => [1 => 1],
    ],
    'list_string' => [
      'allowed_values' => ['string' => 'string'],
    ],
    'timestamp' => [],
    'text' => [],
    'string' => [],
    'decimal' => [],
    'integer' => [],
    'entity_reference' => [
      'target_type' => 'entity_test_constraints',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);
    $this->installEntitySchema($this->entityType);
    $this->createUser();

    foreach ($this->fields as $field_type => $settings) {
      $this->createField($field_type, $settings);
    }
    $constraint = new UniqueFieldConstraint();
    $this->msgTpl = $constraint->message;

    FilterFormat::create([
      'format' => 'my_text_format',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ])->save();
  }

  /**
   * Creates a field of specified type.
   */
  protected function createField($type, $settings = []) {
    $name = 'field_' . $type;

    FieldStorageConfig::create([
      'entity_type' => $this->entityType,
      'field_name' => $name,
      'type' => $type,
      'settings' => $settings,
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'entity_type' => $this->entityType,
      'field_name' => $name,
      'bundle' => $this->entityType,
      'label' => 'Test ' . $type . ' field',
      'translatable' => FALSE,
    ])->save();
  }

  /**
   * Tests the AllowedValues validation constraint validator.
   *
   * @covers ::validate
   * @dataProvider validationDataProvider
   */
  public function testValidation($fieldType, $fieldValue) {

    $fieldName = 'field_' . $fieldType;

    $entity_1 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_1->{$fieldName}->getFieldDefinition()->addConstraint('UniqueField');

    $entity_1->{$fieldName} = $fieldValue;

    /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
    $violations = $entity_1->validate();

    $this->assertCount(0, $violations, 'No validation violations.');
    $entity_1->save();

    $entity_2 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_2->{$fieldName}->getFieldDefinition()->addConstraint('UniqueField');

    $entity_2->{$fieldName} = $fieldValue;

    $this->assertEquals($entity_2->{$fieldName}->value, $entity_1->{$fieldName}->value, 'Field values are equal');

    $violations = $entity_2->validate();
    $this->assertCount(1, $violations, 'One validation violation.');

    /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
    $violation = $violations->get(0);
    // Make sure the information provided by a violation is correct.
    $this->assertEquals($this->msgTpl, $violation->getMessageTemplate(), 'Proper violation message provided.');
  }

  /**
   * Data provider.
   */
  public function validationDataProvider() {
    $dateTime = new \DateTime();

    $data = [];

    $data[] = ['link', 'https://www.drupal.org'];
    $data[] = ['boolean', TRUE];
    $data[] = ['email', 'dries@drupal.org'];
    $data[] = ['telephone', '0123456789'];
    $data[] = ['datetime', $dateTime->format(DateTimeItem::DATETIME_STORAGE_FORMAT)];
    $data[] = ['list_integer', 1];
    $data[] = ['list_string', 'string'];
    $data[] = ['timestamp', $dateTime->getTimestamp()];
    $data[] = ['string', 'string'];
    $data[] = ['decimal', 12.34];
    $data[] = ['integer', 1];

    return $data;
  }

  /**
   * Tests the AllowedValues validator for entity reference.
   *
   * @covers ::validate
   */
  public function testEntityReferenceValidation() {
    $entity_target = $this->entityStorage->create(['name' => $this->randomMachineName()]);
    $entity_target->save();

    $entity_1 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_1->field_entity_reference->getFieldDefinition()->addConstraint('UniqueField');

    $entity_1->field_entity_reference = $entity_target;
    $violations = $entity_1->validate();
    $this->assertCount(0, $violations, 'No validation violations.');
    $entity_1->save();

    $entity_2 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_2->field_entity_reference->getFieldDefinition()->addConstraint('UniqueField');

    $entity_2->field_entity_reference = $entity_target;

    $this->assertEquals($entity_2->field_entity_reference->value, $entity_1->field_entity_reference->value, 'Field values are equal');

    $violations = $entity_2->validate();
    $this->assertCount(1, $violations, 'One validation violation.');

    // Make sure the information provided by a violation is correct.
    $violation = $violations->get(0);
    $this->assertEquals($this->msgTpl, $violation->getMessageTemplate(), 'Proper violation message provided.');
  }

  /**
   * Tests the UniqueFieldValue validation constraint validator for text fields.
   *
   * @covers ::validate
   */
  public function testTextValidation() {
    $fieldName = 'field_text';
    $fieldValue = 'Some text here';

    $entity_1 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_1->{$fieldName}->getFieldDefinition()->addConstraint('UniqueField');

    $entity_1->{$fieldName} = $fieldValue;
    $violations = $entity_1->validate();

    $this->assertCount(0, $violations, 'No validation violations.');
    $entity_1->save();

    $entity_2 = $this->entityStorage->create(['name' => $this->randomMachineName()]);

    $entity_2->{$fieldName}->getFieldDefinition()->addConstraint('UniqueField');

    $entity_2->{$fieldName} = $fieldValue;

    $this->assertEquals($entity_2->{$fieldName}->value, $entity_1->{$fieldName}->value, 'Field values are equal');

    $violations = $entity_2->validate();
    $this->assertCount(1, $violations, 'One validation violation.');

    // Make sure the information provided by a violation is correct.
    $violation = $violations->get(0);
    $this->assertEquals($this->msgTpl, $violation->getMessageTemplate(), 'Proper violation message provided.');
  }

}
