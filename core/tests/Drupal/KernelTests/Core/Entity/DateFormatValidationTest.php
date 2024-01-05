<?php

namespace Drupal\KernelTests\Core\Entity;

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
   * Tests that the pattern of a date format can never be blank.
   *
   * @param bool $locked
   *   Whether the date format entity is locked or not.
   *
   * @testWith [true]
   *   [false]
   */
  public function testPatternCannotBeBlank(bool $locked): void {
    $this->entity->setPattern('')->set('locked', $locked);
    $this->assertValidationErrors([
      'pattern' => 'This value should not be blank.',
    ]);
  }

}
