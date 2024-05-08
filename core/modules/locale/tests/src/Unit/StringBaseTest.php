<?php

declare(strict_types=1);

namespace Drupal\Tests\locale\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\locale\SourceString;
use Drupal\locale\StringStorageException;
use Drupal\Tests\UnitTestCase;

/**
 * @group locale
 */
#[CoversClass(\Drupal\locale\StringBase::class)]
class StringBaseTest extends UnitTestCase {

  public function testSaveWithoutStorage() {
    $string = new SourceString(['source' => 'test']);
    $this->expectException(StringStorageException::class);
    $this->expectExceptionMessage('The string cannot be saved because its not bound to a storage: test');
    $string->save();
  }

  public function testDeleteWithoutStorage() {
    $string = new SourceString(['lid' => 1, 'source' => 'test']);
    $this->expectException(StringStorageException::class);
    $this->expectExceptionMessage('The string cannot be deleted because its not bound to a storage: test');
    $string->delete();
  }

}
