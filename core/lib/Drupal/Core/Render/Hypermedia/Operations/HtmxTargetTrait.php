<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

/**
 * Provides reusable parameters and methods to support the use of hx-target.
 *
 * @see https://htmx.org/attributes/hx-target
 */
trait HtmxTargetTrait {

  /**
   * Specifies the element to target for replacement.
   *
   * The value is one of the following:
   * - A CSS query selector of the element to target.
   * - 'this'
   *   Indicates that element receiving the htmx attributes is the target.
   * - 'closest <CSS selector>'
   *    Finds the closest ancestor element or itself, that matches the given
   *    CSS selector (e.g. 'closest tr' will target the closest table row to
   *    the element).
   * - 'find <CSS selector>'
   *    Finds the first child descendant element that matches the given CSS
   *    selector.
   * - 'next'
   *    Resolves to element.nextElementSibling
   * - 'next <CSS selector>'
   *    Scans the DOM forward for the first element that matches the given CSS
   *    selector. (e.g. 'next .error' will target the closest following sibling
   *    element with the error class)
   * - 'previous' which resolves to element.previousElementSibling
   * - 'previous <CSS selector>'
   *    Scan the DOM backwards for the first element that matches the given CSS
   *    selector. (e.g. `previous .error` will target the closest previous
   *    sibling with the error class)
   */
  protected string $target;

  /**
   * Setter method for $target.
   *
   * @param string $value
   *   The value to set.
   *
   * @return static
   *   The instance using the trait.
   */
  public function target(string $value): static {
    $this->target = $value;
    return $this;
  }

}
