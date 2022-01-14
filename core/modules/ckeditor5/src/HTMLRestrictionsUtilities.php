<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5;

use Masterminds\HTML5\Elements;

/**
 * Utilities for interacting with HTML restrictions.
 *
 * @internal
 *
 * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
 */
final class HTMLRestrictionsUtilities {

  /**
   * Wildcard types, and the methods that return tags the wildcard represents.
   *
   * @var string[]
   */
  private const WILDCARD_ELEMENT_METHODS = [
    '$block' => 'getBlockElementList',
  ];

  /**
   * Gets a list of block level elements.
   *
   * @return array
   *   An array of block level element tags.
   */
  private static function getBlockElementList(): array {
    return array_filter(array_keys(Elements::$html5), function (string $element) {
      return Elements::isA($element, Elements::BLOCK_TAG);
    });
  }

  /**
   * Returns the tags that match the provided wildcard.
   *
   * A wildcard tag in element config is a way of representing multiple tags
   * with a single item, such as `<$block>` to represent all block tags. Each
   * wildcard should have a corresponding callback method listed in
   * WILDCARD_ELEMENT_METHODS that returns the set of tags represented by the
   * wildcard.
   *
   * @param string $wildcard
   *   The wildcard that represents multiple tags.
   *
   * @return array
   *   An array of HTML tags.
   */
  public static function getWildcardTags(string $wildcard):array {
    $wildcard_element_method = self::WILDCARD_ELEMENT_METHODS[$wildcard];
    return call_user_func([self::class, $wildcard_element_method]);
  }

}
