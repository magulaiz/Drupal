<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of date_format entities.
 *
 * @group Entity
 * @group Validation
 */
class DateFormatValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = DateFormat::create([
      'id' => 'test',
      'label' => 'Test',
      'pattern' => 'Y-m-d',
    ]);
    $this->entity->save();
  }

  /**
   * @dataProvider provideLocked
   */
  public function testDateFormatNotNull($locked) {
    $entity_values['id'] = $entity_values['label'] = $this->randomMachineName();
    $entity_values['locked'] = $locked;
    $entity_values['pattern'] = NULL;
    $this->expectException(SchemaIncompleteException::class);
    $this->assertValidationErrors(function () use ($entity_values) {
      DateFormat::create($entity_values)->save();
    });
  }

  /**
   * @dataProvider provideLocked
   */
  public function testDateFormatNotBlank($locked) {
    $entity_values['id'] = $entity_values['label'] = $this->randomMachineName();
    $entity_values['locked'] = $locked;
    $entity_values['pattern'] = '';
    $this->expectException(SchemaIncompleteException::class);
    $entity = DateFormat::create($entity_values);
    $entity->save();
  }

  /**
   * @dataProvider provideLocked
   */
  public function testDateFormatNeedsAtLeastOneDateChar($locked) {
    $entity_values['id'] = $entity_values['label'] = $this->randomMachineName();
    $entity_values['locked'] = $locked;
    $entity_values['pattern'] = 'k';
    $this->expectException(SchemaIncompleteException::class);
    $this->expectExceptionMessageMatches('/At least one of the characters should format this into a date/');
    $entity = DateFormat::create($entity_values);
    $entity->save();
  }

  public function provideLocked(): array {
    return [
      [TRUE],
      [FALSE],
    ];
  }

}
