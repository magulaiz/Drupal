<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\StringTranslation;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the TranslatableMarkup class.
 *
 * @coversDefaultClass \Drupal\Core\StringTranslation\TranslatableMarkup
 * @group StringTranslation
 */
class TranslatableMarkupTest extends UnitTestCase {

  /**
   * Tests that errors are correctly handled when a __toString() fails.
   *
   * @covers ::__toString
   */
  public function testToString(): void {
    $string = 'May I have an exception?';
    $exception = new \Exception('Yes you may.');
    $text = $this->getMockBuilder(TranslatableMarkup::class)
      ->setConstructorArgs([$string, [], []])
      ->onlyMethods(['render'])
      ->getMock();
    $text
      ->expects($this->once())
      ->method('render')
      ->willThrowException($exception);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage($exception->getMessage());
    $this->assertTrue('' === (string) $text);
  }

  /**
   * @covers ::__construct
   */
  public function testIsStringAssertion(): void {
    $translation = $this->getStringTranslationStub();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('$string ("foo") must be a string.');
    new TranslatableMarkup(new TranslatableMarkup('foo', [], [], $translation));
  }

  /**
   * @covers ::__construct
   */
  public function testIsStringAssertionWithFormattableMarkup(): void {
    $formattable_string = new FormattableMarkup('@bar', ['@bar' => 'foo']);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('$string ("foo") must be a string.');
    new TranslatableMarkup($formattable_string);
  }

}
