<?php

namespace Drupal\KernelTests\Core\Extension;

use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the legacy requirements severity deprecations.
 *
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\RequirementSeverity
 * @group extension
 * @group legacy
 */
class LegacyRequirementSeverityTest extends KernelTestBase {

  /**
   * @covers ::getMaxSeverity
   * @dataProvider requirementProvider
   */
  public function testGetMaxSeverity(array $requirements, RequirementSeverity $expectedSeverity): void {
    $this->expectDeprecation('Calling Drupal\Core\Extension\Requirement::getMaxSeverity() with \'severity\' as int values instead of RequirementSeverity enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939');
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
    include_once \DRUPAL_ROOT . '/core/includes/install.inc';

    $info = [
      'title' => 'Foo',
      'severity' => \REQUIREMENT_INFO,
    ];
    $warning = [
      'title' => 'Baz',
      'severity' => \REQUIREMENT_WARNING,
    ];
    $error = [
      'title' => 'Wiz',
      'severity' => \REQUIREMENT_ERROR,
    ];
    $ok = [
      'title' => 'Bar',
      'severity' => \REQUIREMENT_OK,
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
