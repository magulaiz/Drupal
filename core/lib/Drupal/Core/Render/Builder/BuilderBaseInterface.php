<?php

/**
 * @file
 * Contains Drupal\Core\Render\Builder\BuilderBaseInterface.
 */

namespace Drupal\Core\Render\Builder;

/**
 * Defines methods found on the base builder class.
 */
interface BuilderBaseInterface {

  /**
   * Create an instance of a builder object.
   *
   * @return $this
   */
  public static function create();

  /**
   * Set any key on the renderable array being constructed.
   *
   * @param string $key
   *   The key to set on the renderable array.
   * @param string|array $value
   *   The value to assign the key.
   */
  public function set(string $key, mixed $value);

  /**
   * Set the 'prefix' property.
   *
   * @param array $value
   *   The value to assign the property.
   *
   * @return $this
   */
  public function prefix(array $value);

  /**
   * Set the 'suffix' property.
   *
   * @param array $value
   *   The value to assign the property.
   *
   * @return $this
   */
  public function suffix(array $value);

}
