<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5;

use Drupal\Component\Assertion\Inspector;
use Drupal\Component\Utility\Html;
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
   * Formats HTML elements for display.
   *
   * @param array $elements
   *   List of elements to format. The structure is the same as the allowed tags
   *   array documented in FilterInterface::getHTMLRestrictions().
   *
   * @return string[]
   *   A formatted list; a string representation of the given HTML elements.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
   */
  public static function toReadableElements(array $elements): array {
    $readable = [];
    foreach ($elements as $tag => $attributes) {
      $attribute_string = '';
      if (is_array($attributes)) {
        foreach ($attributes as $attribute_name => $attribute_values) {
          if (is_array($attribute_values)) {
            $attribute_values_string = implode(' ', array_keys($attribute_values));
            $attribute_string .= "$attribute_name=\"$attribute_values_string\" ";
          }
          else {
            $attribute_string .= "$attribute_name ";
          }
        }
      }
      $joined = '<' . $tag . (!empty($attribute_string) ? ' ' . trim($attribute_string) : '') . '>';
      array_push($readable, $joined);
    }
    assert(Inspector::assertAllStrings($readable));
    return $readable;
  }

  /**
   * Parses a HTML restrictions string with >=1 tags in an array of single tags.
   *
   * @param string $elements_string
   *   A HTML restrictions string.
   *
   * @return string[]
   *   A list of strings, with a HTML tag and potentially attributes in each.
   */
  public static function allowedElementsStringToPluginElementsArray(string $elements_string): array {
    $html_restrictions = static::allowedElementsStringToHtmlFilterArray($elements_string);
    return static::toReadableElements($html_restrictions);
  }

  /**
   * Parses an HTML string into an array structured as expected by filter_html.
   *
   * @param string $elements_string
   *   A string of HTML tags, potentially with attributes.
   *
   * @return array
   *   An elements array. The structure is the same as the allowed tags array
   *   documented in FilterInterface::getHTMLRestrictions().
   *
   * @see \Drupal\ckeditor5\HTMLRestrictionsUtilities::WILDCARD_ELEMENT_METHODS
   *   Each key in this array represents a valid wildcard tag.
   *
   * @see \Drupal\filter\Plugin\Filter\FilterHtml
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
   */
  public static function allowedElementsStringToHtmlFilterArray(string $elements_string): array {
    preg_match('/<(\$[A-Z,a-z]*)/', $elements_string, $wildcard_matches);

    $wildcard = NULL;
    if (!empty($wildcard_matches)) {
      $wildcard = $wildcard_matches[1];
      assert(substr($wildcard, 0, 1) === '$', 'Wildcard tags must begin with "$"');
      $elements_string = str_replace($wildcard, 'WILDCARD', $elements_string);
    }

    $elements = [];
    $body_child_nodes = Html::load(str_replace('>', ' />', $elements_string))->getElementsByTagName('body')->item(0)->childNodes;

    foreach ($body_child_nodes as $node) {
      if ($node->nodeType !== XML_ELEMENT_NODE) {
        // Skip the empty text nodes inside tags.
        continue;
      }

      $tag = $wildcard ?? $node->tagName;
      if ($node->hasAttributes()) {
        foreach ($node->attributes as $attribute_name => $attribute) {
          $value = empty($attribute->value) ? TRUE : explode(' ', $attribute->value);
          self::addAllowedAttributeToElements($elements, $tag, $attribute_name, $value);
        }
      }
      else {
        if (!isset($elements[$tag])) {
          $elements[$tag] = FALSE;
        }
      }
    }
    return $elements;
  }

  /**
   * Adds allowed attributes to the elements array.
   *
   * @param array $elements
   *   The elements array. The structure is the same as the allowed tags array
   *   documented in FilterInterface::getHTMLRestrictions().
   * @param string $tag
   *   The tag having its attributes configured.
   * @param string $attribute
   *   The attribute being configured.
   * @param array|true $value
   *   The attribute config value.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
   */
  public static function addAllowedAttributeToElements(array &$elements, string $tag, string $attribute, $value): void {
    if (isset($elements[$tag][$attribute]) && $elements[$tag][$attribute] === TRUE) {
      // There's nothing to change as the tag/attribute combination is already
      // set to allow all.
      return;
    }

    if (isset($elements[$tag]) && $elements[$tag] === FALSE) {
      // If the tag is already allowed with no attributes then the value will be
      // FALSE. We need to convert the value to an empty array so that attribute
      // configuration can be added.
      $elements[$tag] = [];
    }

    if ($value === TRUE) {
      $elements[$tag][$attribute] = TRUE;
    }
    else {
      foreach ($value as $attribute_value) {
        $elements[$tag][$attribute][$attribute_value] = TRUE;
      }
    }
  }

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
