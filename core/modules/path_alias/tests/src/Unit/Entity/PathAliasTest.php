<?php

namespace Drupal\Tests\path_alias\Unit\Entity;

use Drupal\path_alias\Entity\PathAlias;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\path_alias\Entity\PathAlias
 * @group path_alias
 */
class PathAliasTest extends UnitTestCase {

  /**
   * @covers ::trimAlias
   * @dataProvider provideAliases
   */
  public function testTrimAlias($alias, $expected) {
    $this->assertEquals($expected, PathAlias::trimAlias($alias));
  }

  public function provideAliases() {
    return [
      ['/', '/'],
      ['//alias', '//alias'],
      ['/first/second/', '/first/second'],
      ['/first/second\\', '/first/second'],
      ['/first/second//', '/first/second'],
      ['/first/second// / ', '/first/second// '],
      ['/first/second//  ', '/first/second'],
    ];
  }

}
