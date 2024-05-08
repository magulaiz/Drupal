<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Unit\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\jsonapi\Query\EntityConditionGroup;
use Drupal\Tests\UnitTestCase;

/**
 * @group jsonapi
 * @internal
 */
#[CoversClass(\Drupal\jsonapi\Query\EntityConditionGroup::class)]
class EntityConditionGroupTest extends UnitTestCase {

  /**
   * @dataProvider constructProvider
   */
  public function testConstruct($case) {
    $group = new EntityConditionGroup($case['conjunction'], $case['members']);

    $this->assertEquals($case['conjunction'], $group->conjunction());

    foreach ($group->members() as $key => $condition) {
      $this->assertEquals($case['members'][$key]['path'], $condition->field());
      $this->assertEquals($case['members'][$key]['value'], $condition->value());
    }
  }

  public function testConstructException() {
    $this->expectException(\InvalidArgumentException::class);
    new EntityConditionGroup('NOT_ALLOWED', []);
  }

  /**
   * Data provider for testConstruct.
   */
  public static function constructProvider() {
    return [
      [['conjunction' => 'AND', 'members' => []]],
      [['conjunction' => 'OR', 'members' => []]],
    ];
  }

}
