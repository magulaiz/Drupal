<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Test\Comparator;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\KernelTests\KernelTestBase;
use Drupal\TestTools\Comparator\MarkupInterfaceComparator;
use SebastianBergmann\Comparator\Factory;

/**
 * Tests \Drupal\TestTools\Comparator\MarkupInterfaceComparator.
 *
 * We need to test the class with a kernel test since casting MarkupInterface
 * objects to strings can require an initialized container.
 *
 * @group Test
 *
 * @coversDefaultClass \Drupal\TestTools\Comparator\MarkupInterfaceComparator
 */
class MarkupInterfaceComparatorTest extends KernelTestBase {

  /**
   * @var \Drupal\TestTools\Comparator\MarkupInterfaceComparator
   */
  protected $comparator;

  /**
   * @var \SebastianBergmann\Comparator\Factory
   */
  protected $factory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->factory = new Factory();
    $this->comparator = new MarkupInterfaceComparator();
    $this->comparator->setFactory($this->factory);
  }

  /**
   * Provides test data for the comparator.
   *
   * @return array
   *   Each array entry has:
   *   - test expected value,
   *   - test actual value,
   *   - a bool indicating the expected return value of ::accepts,
   *   - a value indicating the expected result of ::assertEquals, TRUE if
   *     comparison should match, FALSE if error, or a class name of an object
   *     thrown.
   */
  public static function dataSetProvider() {
    return [
      'FormattableMarkup vs array' => [
        new FormattableMarkup('GoldFinger', []),
        ['GoldFinger'],
        FALSE,
        \RuntimeException::class,
      ],
      'stdClass vs TranslatableMarkup' => [
        (object) ['GoldFinger'],
        new TranslatableMarkup('GoldFinger'),
        FALSE,
        FALSE,
      ],
      'string vs string, equal' => [
        'GoldFinger',
        'GoldFinger',
        FALSE,
        \LogicException::class,
      ],
      'html string with tags vs html string with tags, equal' => [
        '<em class="placeholder">For Your Eyes</em> Only',
        '<em class="placeholder">For Your Eyes</em> Only',
        FALSE,
        \LogicException::class,
      ],
      'html string with tags vs plain string, not equal' => [
        '<em class="placeholder">For Your Eyes</em> Only',
        'For Your Eyes Only',
        FALSE,
        FALSE,
      ],
      'FormattableMarkup with placeholders vs FormattableMarkup with placeholders, equal' => [
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        TRUE,
        TRUE,
      ],
      'FormattableMarkup with placeholders vs FormattableMarkup with placeholders, not equal' => [
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        new FormattableMarkup('%placeholder Too', ['%placeholder' => 'For Your Eyes']),
        TRUE,
        FALSE,
      ],
    ];
  }

  /**
   * Provides test data for the comparator - deprecated cases.
   *
   * @return array
   *   Each array entry has:
   *   - test expected value,
   *   - test actual value,
   *   - a bool indicating the expected return value of ::accepts,
   *   - a value indicating the expected result of ::assertEquals, TRUE if
   *     comparison should match, FALSE if error, or a class name of an object
   *     thrown.
   *   - the expected deprecation message.
   */
  public static function dataSetProviderExceptionCases(): array {
    return [
      'html string with tags vs FormattableMarkup, equal' => [
        '<em class="placeholder">For Your Eyes</em> Only',
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        TRUE,
        TRUE,
        'Can not compare markup between MarkupInterface objects and plain strings',
      ],
      'html string with tags vs FormattableMarkup, not equal' => [
        '<em class="placeholder">For Your Eyes</em> Too',
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        TRUE,
        FALSE,
        'Can not compare markup between MarkupInterface objects and plain strings',
      ],
      'FormattableMarkup vs html string with tags, equal' => [
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        '<em class="placeholder">For Your Eyes</em> Only',
        TRUE,
        TRUE,
        'Can not compare markup between MarkupInterface objects and plain strings',
      ],
      'FormattableMarkup vs html string with tags, not equal' => [
        new FormattableMarkup('%placeholder Only', ['%placeholder' => 'For Your Eyes']),
        '<em class="placeholder">For Your Eyes</em> Too',
        TRUE,
        FALSE,
        'Can not compare markup between MarkupInterface objects and plain strings',
      ],
    ];
  }

  /**
   * @covers ::accepts
   * @dataProvider dataSetProvider
   */
  public function testAccepts($expected, $actual, bool $accepts_result, $equals_result): void {
    if ($accepts_result) {
      $this->assertTrue($this->comparator->accepts($expected, $actual));
    }
    else {
      $this->assertFalse($this->comparator->accepts($expected, $actual));
    }
  }

  /**
   * @covers ::assertEquals
   * @dataProvider dataSetProvider
   */
  public function testAssertEquals($expected, $actual, bool $accepts_result, $equals_result): void {
    try {
      $this->assertNull($this->comparator->assertEquals($expected, $actual));
      $this->assertTrue($equals_result);
    }
    catch (\Throwable $e) {
      if ($equals_result === FALSE) {
        $this->assertNotNull($e->getMessage());
      }
      else {
        $this->assertInstanceOf($equals_result, $e);
      }
    }
  }

  /**
   * @covers ::assertEquals
   * @dataProvider dataSetProviderExceptionCases
   * @group legacy
   */
  public function testDeprecatedAssertEquals($expected, $actual, bool $accepts_result, $equals_result, string $deprecation_message): void {
    if ($accepts_result) {
      $this->assertTrue($this->comparator->accepts($expected, $actual));
    }
    else {
      $this->assertFalse($this->comparator->accepts($expected, $actual));
    }

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage($deprecation_message);
    $this->comparator->assertEquals($expected, $actual);
  }

}
