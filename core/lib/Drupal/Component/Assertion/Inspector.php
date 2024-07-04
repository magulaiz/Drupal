<?php

namespace Drupal\Component\Assertion;

/**
 * Generic inspections for the assert() statement.
 *
 * This is a static function collection for inspecting variable contents. All
 * functions in this collection check a variable against an assertion about its
 * structure.
 *
 * Example call:
 * @code
 *   assert(Inspector::assertAllStrings($array));
 * @endcode
 *
 * @ingroup php_assert
 */
class Inspector {

  /**
   * Asserts callback returns TRUE for each member of a traversable.
   *
   * This is less memory intensive than using array_filter() to build a second
   * array and then comparing the arrays. Many of the other functions in this
   * collection alias this function passing a specific callback to make the
   * code more readable.
   *
   * @param callable $callable
   *   Callback function.
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and $callable returns TRUE on
   *   all members.
   *
   * @see http://php.net/manual/language.types.callable.php
   */
  public static function assertAll(callable $callable, $traversable) {
    if (is_iterable($traversable)) {
      return array_all($traversable, fn ($value) => $callable($value));
    }
    return FALSE;
  }

  /**
   * Asserts that all members are strings.
   *
   * Use this only if it is vital that the members not be objects, otherwise
   * test with ::assertAllStringable().
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are strings.
   */
  public static function assertAllStrings($traversable) {
    return static::assertAll('is_string', $traversable);
  }

  /**
   * Asserts all members are strings or objects with magic __toString() method.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are strings or
   *   objects with __toString().
   */
  public static function assertAllStringable($traversable) {
    return static::assertAll(static::assertStringable(...), $traversable);
  }

  /**
   * Asserts argument is a string or an object castable to a string.
   *
   * Use this instead of is_string() alone unless the argument being an object
   * in any way will cause a problem.
   *
   * @param mixed $string
   *   Variable to be examined
   *
   * @return bool
   *   TRUE if $string is a string or an object castable to a string.
   */
  public static function assertStringable($string) {
    return is_string($string) || (is_object($string) && method_exists($string, '__toString'));
  }

  /**
   * Asserts that all members are arrays.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are arrays.
   */
  public static function assertAllArrays($traversable) {
    return static::assertAll('is_array', $traversable);
  }

  /**
   * Asserts that the array is strict.
   *
   * What PHP calls arrays are more formally called maps in most other
   * programming languages. A map is a datatype that associates values to keys.
   * The term 'strict array' here refers to a 0-indexed array in the classic
   * sense found in programming languages other than PHP.
   *
   * @param mixed $array
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable is a 0-indexed array.
   *
   * @see http://php.net/manual/language.types.array.php
   */
  public static function assertStrictArray($array) {
    if (!is_array($array)) {
      return FALSE;
    }
    return array_is_list($array);
  }

  /**
   * Asserts all members are strict arrays.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are strict arrays.
   *
   * @see ::assertStrictArray
   */
  public static function assertAllStrictArrays($traversable) {
    return static::assertAll([__CLASS__, 'assertStrictArray'], $traversable);
  }

  /**
   * Asserts all given keys exist in every member array.
   *
   * Drupal has several data structure arrays that require certain keys be set.
   * You can overload this function to specify a list of required keys. All
   * of the keys must be set for this method to return TRUE.
   *
   * As an example, this assertion tests for the keys of a theme registry.
   *
   * @code
   *   assert(Inspector::assertAllHaveKey(
   *     $arrayToTest, "type", "theme path", "function", "template", "variables", "render element", "preprocess functions"));
   * @endcode
   *
   * Note: If a method requires certain keys to be present it will usually be
   * specific about the data types for the values of those keys. Therefore it
   * will be best to write a specific test for it. Such tests are either bound
   * to the object that uses them, or are collected into one assertion set for
   * the package.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   * @param ...$keys
   *   Keys to be searched for.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members have all keys.
   */
  public static function assertAllHaveKey($traversable) {
    $args = func_get_args();
    unset($args[0]);

    return static::assertAll(
      fn ($member) => array_all(
        $args,
        fn ($key) => array_key_exists($key, $member),
      ),
      $traversable,
    );
  }

  /**
   * Asserts that all members are integer values.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are integers.
   */
  public static function assertAllIntegers($traversable) {
    return static::assertAll('is_int', $traversable);
  }

  /**
   * Asserts that all members are float values.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are floating point
   *   numbers.
   */
  public static function assertAllFloat($traversable) {
    return static::assertAll('is_float', $traversable);
  }

  /**
   * Asserts that all members are callable.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are callable.
   */
  public static function assertAllCallable($traversable) {
    return static::assertAll('is_callable', $traversable);
  }

  /**
   * Asserts that all members are not empty.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members not empty.
   */
  public static function assertAllNotEmpty($traversable) {
    return static::assertAll(fn ($value) => !empty($value), $traversable);
  }

  /**
   * Asserts all members are numeric data types or strings castable to such.
   *
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are numeric.
   */
  public static function assertAllNumeric($traversable) {
    return static::assertAll('is_numeric', $traversable);
  }

  /**
   * Asserts that all members are strings that contain the specified string.
   *
   * This runs faster than the regular expression equivalent.
   *
   * @param string $pattern
   *   The needle to find.
   * @param mixed $traversable
   *   Variable to examine.
   * @param bool $case_sensitive
   *   TRUE to use strstr(), FALSE to use stristr() which is case insensitive.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are strings
   *   containing $pattern.
   */
  public static function assertAllMatch($pattern, $traversable, $case_sensitive = FALSE) {
    $callable = $case_sensitive ? 'strstr' : 'stristr';
    return static::assertAll(
      fn ($value) => is_string($value) && $callable($value, $pattern) !== FALSE,
      $traversable,
    );
  }

  /**
   * Asserts that all members are strings matching a regular expression.
   *
   * @param string $pattern
   *   Regular expression string to find.
   * @param mixed $traversable
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are strings
   *   matching $pattern.
   */
  public static function assertAllRegularExpressionMatch($pattern, $traversable) {
    return static::assertAll(
      fn ($value) => is_string($value) && preg_match($pattern, $value),
      $traversable,
    );
  }

  /**
   * Asserts that all members are objects.
   *
   * When testing if a collection is composed of objects those objects should
   * be given a common interface to implement and the test should be written to
   * search for just that interface. While this method will allow tests for
   * just object status or for multiple classes and interfaces this was done to
   * allow tests to be written for existing code without altering it. Only use
   * this method in that manner when testing code from third party vendors.
   *
   * Here are some examples:
   * @code
   *   // Just test all are objects, like a cache.
   *   assert(Inspector::assertAllObjects($collection));
   *
   *   // Test if traversable objects (arrays won't pass this)
   *   assert(Inspector::assertAllObjects($collection, '\\Traversable'));
   *
   *   // Test for the Foo class or Bar\None interface
   *   assert(Inspector::assertAllObjects($collection, '\\Foo', '\\Bar\\None'));
   * @endcode
   *
   * @param mixed $traversable
   *   Variable to be examined.
   * @param ...$classes
   *   Classes and interfaces to test objects against.
   *
   * @return bool
   *   TRUE if $traversable can be traversed and all members are objects with
   *   at least one of the listed classes or interfaces.
   */
  public static function assertAllObjects($traversable) {
    $args = func_get_args();
    unset($args[0]);

    return static::assertAll(function($member) use ($args) {
      if (count($args) > 0) {
        return array_any($args, fn($instance) => $member instanceof $instance);
      }
      return \is_object($member);
    }, $traversable);
  }

}
