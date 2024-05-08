<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Unit\Plugin\filter;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * @group views
 */
#[CoversClass(\Drupal\views\Plugin\views\filter\FilterPluginBase::class)]
class FilterPluginBaseTest extends UnitTestCase {

  /**
   * @dataProvider acceptExposedInputProvider
   */
  public function testAcceptExposedInput(bool $expected_result, array $options, array $input) {
    $definition = [
      'title' => 'Accept exposed input Test',
      'group' => 'Test',
    ];
    $filter = new FilterPluginBaseStub([], 'stub', $definition);
    $filter->options = $options;
    $this->assertSame($expected_result, $filter->acceptExposedInput($input));
  }

  /**
   * The data provider for testAcceptExposedInput.
   *
   * @return array
   *   The data set.
   */
  public static function acceptExposedInputProvider() {
    return [
      'not-exposed' => [TRUE, ['exposed' => FALSE], []],
      'exposed-no-input' => [TRUE, ['exposed' => TRUE], []],
      'exposed-zero-input' => [FALSE, [
        'exposed' => TRUE,
        'is_grouped' => FALSE,
        'expose' => [
          'use_operator' => TRUE,
          'operator_id' => '=',
          'identifier' => 'identifier',
        ],
      ], ['identifier' => 0],
      ],
      'exposed-empty-array-input' => [FALSE, [
        'exposed' => TRUE,
        'is_grouped' => FALSE,
        'expose' => [
          'use_operator' => TRUE,
          'operator_id' => '=',
          'identifier' => 'identifier',
        ],
      ], ['identifier' => []],
      ],
    ];
  }

}

class FilterPluginBaseStub extends FilterPluginBase {}
