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
   * Tests that the pattern of a date format is validated.
   *
   * @param string $pattern
   *   The pattern to set.
   * @param bool $locked
   *   Whether the date format entity is locked or not.
   * @param string $expected_error
   *   The error message that should be flagged for the invalid pattern.
   *
   * @testWith ["q", true, "This value is not valid."]
   *   ["", true, "This value should not be blank."]
   *   ["q", false, "This value is not valid."]
   *   ["", false, "This value should not be blank."]
   */
  public function testPatternIsValidated(string $pattern, bool $locked, string $expected_error): void {
    $this->entity->setPattern($pattern)->set('locked', $locked);
    $this->assertValidationErrors(['pattern' => $expected_error]);
  }

}
