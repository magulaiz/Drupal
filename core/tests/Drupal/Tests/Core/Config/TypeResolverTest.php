<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Config;

use Drupal\Core\Config\Schema\TypeResolver;
use Drupal\Tests\UnitTestCase;

/**
 * @covers \Drupal\Core\Config\Schema\TypeResolver
 * @group config
 */
class TypeResolverTest extends UnitTestCase {

  /**
   * @dataProvider providerInvalidTypes
   */
  public function testInvalidType($name, $message, $data = []): void {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage($message);
    TypeResolver::resolveDynamicTypeName($name, $data);
  }

  public function providerInvalidTypes() {
    return [
      'invalid %variable' => [
        '[foo.%bar.qux]',
        '`foo.%bar.qux` is not a valid dynamic type expression. Dynamic type expressions must contain at least `%parent`, `%key`, or `%type`.`',
        ['foo' => 'foo'],
      ],
      'misspelling' => [
        '[%paren.field_type]',
        '`%paren.field_type` is not a valid dynamic type expression. Dynamic type expressions must contain at least `%parent`, `%key`, or `%type`.`',
      ],
      'type without parent' => [
        '[something.%type]',
        '`%type` can only used when immediately proceeded by `%parent` in `something.%type`',
        ['something' => 'something'],
      ],
    ];
  }

}
