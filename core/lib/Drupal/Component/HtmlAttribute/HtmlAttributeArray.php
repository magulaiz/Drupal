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
   * {@inheritdoc}
   */
  public function offsetGet(mixed $offset): mixed {
    return $this->value[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet(mixed $offset, mixed $value): void {
    if (isset($offset)) {
      $this->value[$offset] = $value;
    }
    else {
      $this->value[] = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset(mixed $offset): void {
    unset($this->value[$offset]);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists(mixed $offset): bool {
    return isset($this->value[$offset]);
  }

  /**
   * Implements the magic __toString() method.
   */
  public function __toString(): string {
    // Filter out any empty values before printing.
    $this->value = array_unique(array_filter($this->value));
    return Html::escape(implode(' ', $this->value));
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->value);
  }

  /**
   * Exchange the array for another one.
   *
   * @see ArrayObject::exchangeArray
   *
   * @param array $input
   *   The array input to replace the internal value.
   *
   * @return array
   *   The old array value.
   */
  public function exchangeArray(array $input): array {
    $old = $this->value;
    $this->value = $input;
    return $old;
  }

}
