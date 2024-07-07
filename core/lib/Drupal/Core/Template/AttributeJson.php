<?php

namespace Drupal\Core\Template;

use Drupal\Component\Utility\Xss;

/**
 * Represents an attribute containing JSON formatted data.
 */
class AttributeJson extends AttributeValueBase {

  const RENDER_EMPTY_ATTRIBUTE = FALSE;

  /**
   * Encode the array as JSON.
   *
   * @return string
   *   The JSON encoded array or empty string on failure.
   */
  public function __toString() {
    $value = $this->value();
    $value = is_array($value) ? $value : [$value];
    // Escape the values.
    $data = [];
    foreach ($value as $key => $item) {
      $data[Xss::filter($key)] = is_string($item) ? Xss::filter($item) : $item;
    }
    $string = json_encode($data, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
    return $string ? $string : '';
  }

}
