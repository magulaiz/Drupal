<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Extension\Requirement;

include_once \DRUPAL_ROOT . '/core/includes/install.inc';

use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\RequirementSeverity
 *
 * @group Extension
 */
class RequirementSeverityTest extends UnitTestCase {

  /**
   * @covers ::convertLegacyIntSeveritiesToEnums
   * @group legacy
   */
  public function testConvertLegacySeverities(): void {
    $requirements['foo'] = [
      'title' => new TranslatableMarkup('Foo'),
      'severity' => \REQUIREMENT_INFO,
    ];
    $requirements['bar'] = [
      'title' => new TranslatableMarkup('Bar'),
      'severity' => \REQUIREMENT_ERROR,
    ];
    $this->expectDeprecation(
      'Calling methods with an array of $requirements with \'severity\' as int values instead of Drupal\Core\Extension\Requirement\RequirementSeverity enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939'
    );
    RequirementSeverity::convertLegacyIntSeveritiesToEnums($requirements);
    $this->assertEquals(
      RequirementSeverity::INFO,
      $requirements['foo']['severity']
    );
    $this->assertEquals(
      RequirementSeverity::ERROR,
      $requirements['bar']['severity']
    );
  }

  /**
   * @covers ::isMoreSevereThan
   */
  public function testIsLessThan(): void {
    $this->assertTrue(
      RequirementSeverity::ERROR->isMoreSevereThan(RequirementSeverity::WARNING)
    );
    $this->assertTrue(
      RequirementSeverity::WARNING->isMoreSevereThan(RequirementSeverity::OK)
    );
    $this->assertTrue(
      RequirementSeverity::OK->isMoreSevereThan(RequirementSeverity::INFO)
    );
  }

  /**
   * @covers ::maxSeverityFromRequirements
   * @dataProvider requirementProvider
   */
  public function testGetMaxSeverity(
    array $requirements,
    RequirementSeverity $expectedSeverity
  ): void {
    $severity = RequirementSeverity::maxSeverityFromRequirements($requirements);
    $this->assertEquals($expectedSeverity, $severity);
  }

  /**
   * Data provider for requirement helper test.
   */
  public static function requirementProvider(): array {
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
      'error is most severe' => [
        [
          $info,
          $error,
          $ok,
        ],
        RequirementSeverity::ERROR,
      ],
      'ok is most severe' => [
        [
          $info,
          $ok,
        ],
        RequirementSeverity::OK,
      ],
      'warning is most severe' => [
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
