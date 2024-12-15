<?php

namespace Drupal\Core\Template;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Helper class to deal with mixed array and Attribute operations.
 *
 * This class contains static methods only and is not meant to be instantiated.
 */
class AttributeHelper {

  /**
   * This class should not be instantiated.
   */
  private function __construct() {
  }

  /**
   * Checks if the given attribute collection has an attribute.
   *
   * @param string $name
   *   The name of the attribute to check for.
   * @param HtmlAttributeInterface|array $collection
   *   An Attribute object or an array of attributes.
   *
   * @return bool
   *   TRUE if the attribute exists, FALSE otherwise.
   *
   * @throws \InvalidArgumentException
   *   When the input $collection is neither an Attribute object nor an array.
   */
  public static function attributeExists($name, $collection) {
    if ($collection instanceof HtmlAttributeInterface) {
      return $collection->hasAttribute($name);
    }
    elseif (is_array($collection)) {
      return array_key_exists($name, $collection);
    }
    throw new \InvalidArgumentException('Invalid collection argument');
  }

  /**
   * Merges two attribute collections.
   *
   * @param HtmlAttributeInterface|array $a
   *   First Attribute object or array to merge. The returned value type will
   *   be the same as the type of this argument.
   * @param HtmlAttributeInterface|array $b
   *   Second Attribute object or array to merge.
   *
   * @return \Drupal\Core\Template\Attribute|array
   *   The merged attributes in the form of the first argument.
   *
   * @throws \InvalidArgumentException
   *   If at least one collection argument is neither an Attribute object nor an
   *   array.
   */
  public static function mergeCollections($a, $b) {
    if (!($a instanceof HtmlAttributeInterface || is_array($a)) || !($b instanceof HtmlAttributeInterface || is_array($b))) {
      throw new \InvalidArgumentException('Invalid collection argument');
    }
    // If both collections are arrays, just merge them.
    if (is_array($a) && is_array($b)) {
      return NestedArray::mergeDeep($a, $b);
    }
    // Merge through Attribute::merge.
    $merge_a = $a instanceof Attribute ? $a : new Attribute($a);
    $merge_b = $b instanceof Attribute ? $b : new Attribute($b);
    $merge_a->merge($merge_b);
    // If we started with an Attribute, return an Attribute.
    // If we started with an array, return an array.
    return $a instanceof Attribute ? $merge_a : $merge_a->toArray();
  }

}
