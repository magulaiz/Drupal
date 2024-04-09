<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Render\Element;

use Drupal\Core\Render\Element\StatusReport;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the status report element legacy methods.
 *
 * @group Render
 * @group legacy
 */
class LegacyStatusReportTest extends KernelTestBase {

  /**
   * Tests the getSeverities() method deprecation.
   */
  public function testGetSeveritiesDeprecation() {
    $this->expectDeprecation('Calling Drupal\Core\Render\Element\StatusReport::getSeverities() is deprecated in drupal:10.3.0 and is removed from in drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3410939');
    $severities = StatusReport::getSeverities();
    $this->assertIsArray($severities);
  }

}
