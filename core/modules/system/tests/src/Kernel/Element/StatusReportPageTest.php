<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Element;

use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\KernelTests\KernelTestBase;
use Drupal\system\Element\StatusReportPage;

include_once \DRUPAL_ROOT . '/core/includes/install.inc';

/**
 * Tests the status report page element.
 *
 * @group system
 * @group legacy
 */
class StatusReportPageTest extends KernelTestBase {

  /**
   * Tests the status report page element.
   */
  public function testPeRenderCounters(): void {
    $element = [
      '#requirements' => [
        'foo' => [
          'title' => 'Foo',
          'severity' => RequirementSeverity::INFO,
        ],
        'baz' => [
          'title' => 'Baz',
          'severity' => RequirementSeverity::WARNING,
        ],
        'wiz' => [
          'title' => 'Wiz',
          'severity' => RequirementSeverity::ERROR,
        ],
        'bar' => [
          'title' => 'Bar',
          'severity' => RequirementSeverity::OK,
        ],
        'legacy' => [
          'title' => 'Legacy',
          'severity' => \REQUIREMENT_OK,
        ],
      ],
    ];
    $this->expectDeprecation('Calling Drupal\system\Element\StatusReportPage::preRenderCounters() with \'severity\' as int values instead of RequirementSeverity enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939');
    $element = StatusReportPage::preRenderCounters($element);

    $error = $element['#counters']['error'];
    $this->assertEquals(1, $error['#amount']);
    $this->assertEquals('error', $error['#severity']);

    $warning = $element['#counters']['warning'];
    $this->assertEquals(1, $warning['#amount']);
    $this->assertEquals('warning', $warning['#severity']);

    $checked = $element['#counters']['checked'];
    $this->assertEquals(1, $checked['#amount']);
    $this->assertEquals('checked', $checked['#severity']);

  }

}
