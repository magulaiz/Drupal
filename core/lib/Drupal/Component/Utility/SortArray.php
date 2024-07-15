<?php

namespace Drupal\Component\Utility;

/**
 * Provides generic array sorting helper methods.
 *
 * @ingroup utility
 */
class SortArray {

  /**
   * Sorts a structured array by 'weight' element in numeric ascending order.
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that optionally include a 'weight' element. For items without a
   *   'weight' element, a default value of 0 will be used.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   *
   * @see static::sortByKeyInt()
   */
  public static function sortByWeightElement(array $a, array $b) {
    return static::sortByKeyInt($a, $b, 'weight');
  }

  /**
   * Sorts a structured array by '#weight' property in numeric ascending order.
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that optionally include a '#weight' key. For items without a
   *   '#weight' key, a default value of 0 will be used.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   *
   * @see static::sortByKeyInt()
   */
  public static function sortByWeightProperty($a, $b) {
    return static::sortByKeyInt($a, $b, '#weight');
  }

  /**
   * Sorts a structured array by 'title' key using natural case insensitive sort.
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that optionally include a 'title' key. For items without a 'title'
   *   key, an empty string will be used.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   *
   * @see static::sortByKeyString()
   */
  public static function sortByTitleElement($a, $b) {
    return static::sortByKeyString($a, $b, 'title');
  }

  /**
   * Sorts a structured array by '#title' property using natural case insensitive sort.
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that optionally include a '#title' key. For items without a '#title'
   *   key, an empty string will be used.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   *
   * @see static::sortByKeyString()
   */
  public static function sortByTitleProperty($a, $b) {
    return static::sortByKeyString($a, $b, '#title');
  }

  /**
   * Sorts a string array item by an arbitrary key using natural case insensitive sort.
   *
   * @param array $a
   *   First item for comparison.
   * @param array $b
   *   Second item for comparison.
   * @param string $key
   *   The key to use in the comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByKeyString($a, $b, $key) {
    $a_title = (is_array($a) && isset($a[$key])) ? $a[$key] : '';
    $b_title = (is_array($b) && isset($b[$key])) ? $b[$key] : '';

    return strnatcasecmp($a_title, $b_title);
  }

  /**
   * Sorts an integer array item by an arbitrary key in numeric ascending order.
   *
   * @param array $a
   *   First item for comparison.
   * @param array $b
   *   Second item for comparison.
   * @param string $key
   *   The key to use in the comparison.
   *
   * @return int
   *   The comparison result for uasort().
   *
   *   Since this method will return zero if the value is not defined for the given
   *   key, it ensures that items without the specified key are treated as having
   *   a weight of zero.
   */
  public static function sortByKeyInt($a, $b, $key) {
    $a_weight = (is_array($a) && isset($a[$key])) ? $a[$key] : 0;
    $b_weight = (is_array($b) && isset($b[$key])) ? $b[$key] : 0;

    return $a_weight <=> $b_weight;
  }

}
