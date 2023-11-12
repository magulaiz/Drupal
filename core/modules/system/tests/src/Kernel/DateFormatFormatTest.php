<?php

namespace Drupal\Tests\system\Kernel;

use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @group system
 */
class DateFormatFormatTest extends KernelTestBase {

  use UserCreationTrait {
    createUser as drupalCreateUser;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * @dataProvider provideLocked
   */
  public function testDateFormatNotNull($locked) {
    $entity_values['id'] = $entity_values['label'] = $this->randomMachineName();
    $entity_values['locked'] = $locked;
    $entity_values['pattern'] = NULL;
    $this->expectException(SchemaIncompleteException::class);
    $entity = DateFormat::create($entity_values);
    $entity->save();
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

  public function provideLocked(): array
  {
    return [
      [TRUE],
      [FALSE],
    ];
  }

}
