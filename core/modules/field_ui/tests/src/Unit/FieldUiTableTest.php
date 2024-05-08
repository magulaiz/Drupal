<?php

declare(strict_types=1);

namespace Drupal\Tests\field_ui\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\UnitTestCase;

/**
 * @group field_ui
 */
#[CoversClass(\Drupal\field_ui\Element\FieldUiTable::class)]
class FieldUiTableTest extends UnitTestCase {

  /**
   * @dataProvider providerTestReduceOrder
   */
  public function testReduceOrder($array, $expected) {
    $this->assertSame($expected, array_reduce($array, ['Drupal\field_ui\Element\FieldUiTable', 'reduceOrder']));
  }

  /**
   * Provides test data for testReduceOrder().
   */
  public static function providerTestReduceOrder() {
    return [
      'Flat' => [
        'array' => [
          [
            'name' => 'foo',
          ],
          [
            'name' => 'bar',
          ],
          [
            'name' => 'baz',
          ],
        ],
        'expected' => ['foo', 'bar', 'baz'],
      ],
      'Nested' => [
        'array' => [
          [
            'name' => 'foo',
            'children' => [
              [
                'name' => 'bar',
                'weight' => 0,
              ],
              [
                'name' => 'baz',
                'weight' => -1,
              ],
            ],
          ],
          [
            'name' => 'biz',
          ],
        ],
        'expected' => ['foo', 'baz', 'bar', 'biz'],
      ],
      'Nested no name key' => [
        'array' => [
          [
            'children' => [
              [
                'name' => 'foo',
                'weight' => -1,
              ],
              [
                'name' => 'bar',
                'weight' => 1,
              ],
              [
                'name' => 'baz',
                'weight' => 0,
              ],
            ],
          ],
          [
            'name' => 'biz',
          ],
        ],
        'expected' => ['foo', 'baz', 'bar', 'biz'],
      ],
    ];
  }

}
