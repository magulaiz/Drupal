<?php

namespace Drupal\Component\Utility;

/**
 * Provides helpers to perform diffs on multi dimensional arrays.
 *
 * @ingroup utility
 */
class DiffArray {

  /**
   * Recursively computes the difference of arrays with additional index check.
   *
   * This is a version of array_diff_assoc() that supports multidimensional
   * arrays.
   *
   * @param array $array1
   *   The array to compare from.
   * @param array $array2
   *   The array to compare to.
   *
   * @return array
   *   Returns an array containing all the values from array1 that are not present
   *   in array2.
   */
  public static function diffAssocRecursive(array $array1, array $array2) {
    $difference = [];

    foreach ($array1 as $key => $value) {
      if (is_array($value)) {
        if (!array_key_exists($key, $array2) || !is_array($array2[$key])) {
          $difference[$key] = $value;
        }
        else {
          $new_diff = static::diffAssocRecursive($value, $array2[$key]);
          if (!empty($new_diff)) {
            $difference[$key] = $new_diff;
          }
        }
      }
      elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
        $difference[$key] = $value;
      }
    }

    return $difference;
  }

  /**
   * Computes the difference of arrays.
   *
   * The main difference from the array_diff() is that this method does not
   * remove duplicates. For example:
   * @code
   *   array_diff([1, 1, 1], [1]); // []
   *   \Drupal\Component\Utility\DiffArray::diffOnce([1, 1, 1], [1]); // [1, 1]
   * @endcode
   *
   * Keys are maintained from the $array1.
   *
   * The comparison of items is always performed in the strict (===) mode.
   *
   * @param array $array1
   *   The array to compare from.
   * @param array $array2
   *   The array to compare to.
   *
   * @return array
   *   Returns the difference between the two arrays.
   */
  public static function diffOnce(array $array1, array $array2) {
    foreach ($array2 as $item) {
      // Always use strict mode because otherwise there could be fatal errors on
      // object conversions.
      $key = array_search($item, $array1, TRUE);
      if ($key !== FALSE) {
        unset($array1[$key]);
      }
    }
    return $array1;
  }

}
