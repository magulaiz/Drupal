<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Extension\Requirement;

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
  public function testGetMaxSeverity(array $requirements, RequirementSeverity $expectedSeverity): void {
    $severity = RequirementSeverity::getMaxSeverity($requirements);
    $this->assertEquals($expectedSeverity, $severity);
  }

  /**
   * Data provider for requirement helper test.
   *
   * @return array
   *   Test data.
   */
  public function requirementProvider(): array {
    $info = [
      'title' => 'Foo',
      'severity' => RequirementSeverity::INFO,
    ];
    $warning = [
      'title' => 'Baz',
      'severity' => RequirementSeverity::WARNING,
    ];
    $error = [
      'title' => 'Wiz',
      'severity' => RequirementSeverity::ERROR,
    ];
    $ok = [
      'title' => 'Bar',
      'severity' => RequirementSeverity::OK,
    ];
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
