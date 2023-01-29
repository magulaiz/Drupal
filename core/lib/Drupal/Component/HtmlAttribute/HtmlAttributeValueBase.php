<?php

declare(strict_types=1);

namespace Drupal\Component\HtmlAttribute;

use Drupal\Component\Utility\Html;

/**
 * Defines the base class for an attribute type.
 *
 * @see \Drupal\Component\HtmlAttribute\HtmlAttributeCollection
 */
abstract class HtmlAttributeValueBase {

  /**
   * Renders '$name=""' if $value is an empty string.
   *
   * @see \Drupal\Component\HtmlAttribute\HtmlAttributeValueBase::render()
   */
  const RENDER_EMPTY_ATTRIBUTE = TRUE;

  /**
   * Constructs a \Drupal\Component\HtmlAttribute\HtmlAttributeValueBase object.
   *
   * @param string $name
   *   The name of the value.
   * @param scalar|array<scalar>|null $value
   *   The value itself.
   */
  public function __construct(
    protected string $name,
    protected string|int|bool|float|array|NULL $value,
  ) {
  }

  /**
   * Returns a string representation of the attribute.
   *
   * While __toString only returns the value in a string form, render()
   * contains the name of the attribute as well.
   *
   * @return string
   *   The string representation of the attribute.
   */
  public function render(): string {
    $value = (string) $this;
    if (isset($this->value) && static::RENDER_EMPTY_ATTRIBUTE || !empty($value)) {
      return Html::escape($this->name) . '="' . $value . '"';
    }
    return '';
  }

  /**
   * Returns the raw value.
   *
   * @return scalar|array<scalar>|null
   *   The raw value.
   */
  public function value(): string|int|bool|float|array|NULL {
    return $this->value;
  }

  /**
   * Implements the magic __toString() method.
   */
  abstract public function __toString(): string;

}
