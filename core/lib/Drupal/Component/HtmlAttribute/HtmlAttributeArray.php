<?php

declare(strict_types=1);

namespace Drupal\Component\HtmlAttribute;

use Drupal\Component\Utility\Html;

/**
 * A class defining a type of HtmlAttribute that can be added to as an array.
 *
 * To use with HtmlAttributeCollection, the array must be specified.
 * Correct:
 * @code
 *  $attributes = new HtmlAttributeCollection();
 *  $attributes['class'] = [];
 *  $attributes['class'][] = 'cat';
 * @endcode
 * Incorrect:
 * @code
 *  $attributes = new HtmlAttributeCollection();
 *  $attributes['class'][] = 'cat';
 * @endcode
 *
 * @see \Drupal\Component\HtmlAttribute\HtmlAttributeCollection
 *
 * @implements \ArrayAccess<string, scalar>
 * @implements \IteratorAggregate<string, scalar>
 */
class HtmlAttributeArray extends HtmlAttributeValueBase implements \ArrayAccess, \IteratorAggregate {

  /**
   * Ensures empty array as a result of array_filter will not print '$name=""'.
   *
   * @see \Drupal\Component\HtmlAttribute\HtmlAttributeArray::__toString()
   * @see \Drupal\Component\HtmlAttribute\HtmlAttributeValueBase::render()
   */
  const RENDER_EMPTY_ATTRIBUTE = FALSE;

  /**
   * Constructs an HtmlAttributeArray object.
   *
   * @phpstan-param string $name
   *   The name of the value.
   * @phpstan-param array<scalar> $arrayValue
   *   The value itself.
   */
  public function __construct(
    string $name,
    private array $arrayValue,
  ) {
    parent::__construct($name);
  }

  /**
   * Returns the raw value.
   *
   * @phpstan-return array<scalar>
   *   The raw value.
   */
  protected function doGetValue(): array {
    return $this->arrayValue;
  }

  /**
   * Returns the value at the specified index.
   *
   * @phpstan-param string|int $key
   *
   * @phpstan-return scalar
   */
  public function offsetGet(mixed $key): bool|float|int|string {
    return $this->arrayValue[$key];
  }

  /**
   * Sets the value at the specified index.
   *
   * @phpstan-param string|int|null $key
   * @phpstan-param scalar $value
   */
  public function offsetSet(mixed $key, mixed $value): void {
    if (isset($key)) {
      $this->arrayValue[$key] = $value;
    }
    else {
      $this->arrayValue[] = $value;
    }
  }

  /**
   * Unsets the value at the specified index.
   *
   * @phpstan-param string|int $key
   */
  public function offsetUnset(mixed $key): void {
    unset($this->arrayValue[$key]);
  }

  /**
   * Returns whether the requested index exists.
   *
   * @phpstan-param string|int $key
   */
  public function offsetExists(mixed $key): bool {
    return isset($this->arrayValue[$key]);
  }

  /**
   * Implements the magic __toString() method.
   */
  public function __toString(): string {
    // Filter out any empty values before printing.
    $this->arrayValue = array_unique(array_filter($this->arrayValue));
    return Html::escape(implode(' ', $this->arrayValue));
  }

  /**
   * Retrieves an external iterator.
   *
   * @phpstan-return \ArrayIterator<string|int, scalar>
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->arrayValue);
  }

  /**
   * Exchange the array for another one.
   *
   * @see ArrayObject::exchangeArray
   *
   * @phpstan-param array<scalar> $input
   *   The array input to replace the internal value.
   *
   * @phpstan-return array<scalar>
   *   The old array value.
   */
  public function exchangeArray(array $input): array {
    $old = $this->arrayValue;
    $this->arrayValue = $input;
    return $old;
  }

}
