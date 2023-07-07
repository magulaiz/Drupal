<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

/**
 * Cleans up exception objects so they can be serialized.
 */
class ExceptionCleaner {

  /**
   * Modifies an exception to make it serializable.
   *
   * @param \Throwable $e
   *   Exception or error to clean up.
   */
  public function cleanException(\Throwable $e) {
    // Look for properties that can be cleaned.
    $property_filter = NULL;
    $rc = new \ReflectionClass($e);
    do {
      foreach ($rc->getProperties($property_filter) as $property) {
        $value = $property->getValue($e);
        if ($value instanceof \Throwable) {
          $this->cleanException($value);
        }
        elseif ($property->name === 'trace' && is_array($value)) {
          $this->cleanArray($value);
          $property->setValue($e, $value);
        }
        try {
          serialize($value);
        }
        catch (\Throwable $ee) {
          throw $ee;
        }
      }
      $property_filter = \ReflectionProperty::IS_PRIVATE;
    } while ($rc = $rc->getParentClass());

    try {
      serialize($e);
    }
    catch (\Throwable $ee) {
      throw $ee;
    }
  }

  /**
   * Cleans an array to make it serializable.
   *
   * @param array $array
   *   The array to clean up.
   */
  private function cleanArray(array &$array): void {
    foreach ($array as &$v) {
      if ($v instanceof \Closure) {
        $v = $this->replaceClosure($v);
      }
      elseif ($v instanceof \Reflector) {
        $v = $this->replaceReflector($v);
      }
      elseif (is_array($v)) {
        $this->cleanArray($v);
      }
      elseif (is_object($v)) {
        try {
          $serialized = serialize($v);
          unserialize($serialized);
        }
        catch (\Throwable) {
          // Cannot serialize or unserialize this object.
          // Insert a placeholder.
          $v = '{' . get_class($v) . ' object}';
        }
      }
      try {
        serialize($v);
      }
      catch (\Throwable $ee) {
        throw $ee;
      }
    }
    try {
      serialize($array);
    }
    catch (\Throwable $eee) {
      throw $eee;
    }
  }

  /**
   * Replaces a closure with a descriptive string.
   *
   * @param \Closure $closure
   *   Closure.
   *
   * @return string
   *   String to insert instead of the closure.
   */
  private function replaceClosure(\Closure $closure): string {
    $rf = new \ReflectionFunction($closure);
    return sprintf(
      '{closure: %s:%d}',
      $rf->getFileName(),
      $rf->getStartLine(),
    );
  }

  /**
   * Replaces a reflector with a descriptive string.
   *
   * @param \Reflector $reflector
   *   Reflector.
   *
   * @return string
   *   String to insert instead of the reflector.
   */
  private function replaceReflector(\Reflector $reflector) {
    return '{' . get_class($reflector) . '}';
  }

}
