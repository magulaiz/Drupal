<?php

namespace Drupal\Tests\Core\Extension\Requirement;

use Drupal\Core\Extension\Requirement\RequirementError;
use Drupal\Core\Extension\Requirement\RequirementHelper;
use Drupal\Core\Extension\Requirement\RequirementInfo;
use Drupal\Core\Extension\Requirement\RequirementInterface;
use Drupal\Core\Extension\Requirement\RequirementOk;
use Drupal\Core\Extension\Requirement\RequirementWarning;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\RequirementHelper
 *
 * @group Extension
 */
class RequirementHelperTest extends UnitTestCase {

  /**
   * @covers ::getMaxSeverity
   * @dataProvider requirementProvider
   *
   * @param RequirementInterface[] $requirements
   * @param int $expectedSeverity
   */
  public function testGetMaxSeverity($requirements, $expectedSeverity) {
    $severity = RequirementHelper::getMaxSeverity($requirements);
    $this->assertEquals($expectedSeverity, $severity);
  }

  /**
   * Data provider for requirement helper test.
   *
   * @return array
   *   Test data.
   */
  public function requirementProvider() {
    $info = RequirementInfo::create()
      ->setValue("Foo");
    $warning = RequirementWarning::create()
      ->setValue("Baz");
    $error = RequirementError::create()
      ->setValue("Baz");
    $ok = RequirementOk::create()
      ->setValue("Bar");
    return [
      [
        [
          $info,
          $error,
          $ok,
        ],
        RequirementInterface::SEVERITY_ERROR,
      ],
      [
        [
          $info,
          $ok,
        ],
        RequirementInterface::SEVERITY_OK,
      ],
      [
        [
          $warning,
          $info,
          $ok,
        ],
        RequirementInterface::SEVERITY_WARNING,
      ],
    ];
  }
}
