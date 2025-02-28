<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

/**
 * Provides reusable parameters and methods to support the use of hx-select.
 *
 * @see https://htmx.org/attributes/hx-select
 */
trait HtmxSelectTrait {


  /**
   * Specifies the markup to select from the returned request.
   *
   * A CSS query selector of the element or elements to select from the
   * response.
   */
  protected string $select;

  /**
   * Setter method for $select.
   *
   * @param string $value
   *   The value to set.
   *
   * @return static
   *   The instance using the trait.
   */
  public function select(string $value): static {
    $this->select = $value;
    return $this;
  }

}
