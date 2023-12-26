<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Extension\Requirement;

use Drupal\Core\Extension\Requirement\Requirement;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\RequirementSeverity
 *
 * @group Extension
 */
class RequirementSeverityTest extends UnitTestCase {

  /**
   * @covers ::getMaxSeverity
   * @dataProvider requirementProvider
   */
  public function testGetMaxSeverity(array $requirements, RequirementSeverity $expectedSeverity) {
    $severity = RequirementSeverity::getMaxSeverity($requirements);
    $this->assertEquals($expectedSeverity, $severity);
  }

  /**
   * Data provider for requirement helper test.
   *
   * @return array
   *   Test data.
   */
  public function requirementProvider() {
    $info = Requirement::create()
      ->setSeverity(RequirementSeverity::INFO)
      ->setValue("Foo");
    $warning = Requirement::create()
      ->setSeverity(RequirementSeverity::WARNING)
      ->setValue("Baz");
    $error = Requirement::create()
      ->setSeverity(RequirementSeverity::ERROR)
      ->setValue("Baz");
    $ok = Requirement::create()
      ->setValue("Bar");
    return [
      [
        [
          $info,
          $error,
          $ok,
        ],
        RequirementSeverity::ERROR,
      ],
      [
        [
          $info,
          $ok,
        ],
        RequirementSeverity::OK,
      ],
      [
        [
          $warning,
          $info,
          $ok,
        ],
        RequirementSeverity::WARNING,
      ],
    ];
  }

}
