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
        'The only valid usages of a variable value with a % in it are %parent, %key, and %type in foo.%bar.qux',
        ['foo' => 'foo'],
      ],
      'misspelling' => [
        '[%paren.field_type]',
        'The only valid usages of a variable value with a % in it are %parent, %key, and %type in %paren.field_type',
      ],
      'type without parent' => [
        '[something.%type]',
        '%type can only used when immediately proceeded by %parent in something.%type',
        ['something' => 'something'],
      ],
    ];
  }

}
