<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5;

use Drupal\Component\Assertion\Inspector;
use Drupal\Component\Utility\DiffArray;
use Drupal\Component\Utility\Html;
use Drupal\filter\FilterFormatInterface;
use Drupal\filter\Plugin\FilterInterface;
use Masterminds\HTML5\Elements;

/**
 * Represents a set of HTML restrictions.
 *
 * @todo rename to "supported HTML" or add support for "forbidden" tags
 *
 * @internal
 */
final class HTMLRestrictions implements \Countable {

  /**
   * An array of allowed elements.
   *
   * @var array
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
   */
  protected $elements;

  /**
   * Wildcard types, and the methods that return tags the wildcard represents.
   *
   * @var string[]
   */
  private const WILDCARD_ELEMENT_METHODS = [
    '$block' => 'getBlockElementList',
  ];

  /**
   * Constructs a set of HTML restrictions.
   *
   * @param array $elements
   *   The allowed elements.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions()
   */
  public function __construct(array $elements) {
    self::validateAllowedRestrictionsPhase1($elements);
    self::validateAllowedRestrictionsPhase2($elements);
    self::validateAllowedRestrictionsPhase3($elements);
    self::validateAllowedRestrictionsPhase4($elements);
    $this->elements = $elements;
  }

  /**
   * Validates allowed elements — phase 1: shape of keys.
   *
   * @param array $elements
   *   The allowed elements.
   *
   * @throws \InvalidArgumentException
   */
  private static function validateAllowedRestrictionsPhase1(array $elements): void {
    if (!is_array($elements) || !Inspector::assertAllStrings(array_keys($elements))) {
      throw new \InvalidArgumentException('An array of key-value pairs must be provided, with HTML tag names as keys.');
    }
    foreach (array_keys($elements) as $html_tag_name) {
      if (trim($html_tag_name) !== $html_tag_name) {
        throw new \InvalidArgumentException(sprintf('The "%s" HTML tag contains whitespace. Omit the whitespace.', $html_tag_name));
      }
      if ($html_tag_name[0] === '<' || $html_tag_name[-1] === '>') {
        throw new \InvalidArgumentException(sprintf('"%s" is not a HTML tag name, it is an actual HTML tag. Omit the angular brackets.', $html_tag_name));
      }
      // @todo conform to HTML element naming rules ………………………… — exception is *
      //      if (…) {
      //        throw new \InvalidArgumentException(sprintf('"%s" is not a valid HTML5 element name.', $key));
      //      }
    }
  }

  /**
   * Validates allowed elements — phase 2: shape of values.
   *
   * @param array $elements
   *   The allowed elements.
   *
   * @throws \InvalidArgumentException
   */
  private static function validateAllowedRestrictionsPhase2(array $elements): void {
    foreach ($elements as $html_tag_name => $html_tag_restrictions) {
      // The value must be either a boolean (FALSE means no attributes are
      // allowed, TRUE means all attributes are allowed), or an array of allowed
      // attribute names.
      if (is_bool($html_tag_restrictions)) {
        continue;
      }
      if (!is_array($html_tag_restrictions)) {
        throw new \InvalidArgumentException(sprintf('The value for the "%s" HTML tag is neither a boolean nor an array of attribute restrictions.', $html_tag_name));
      }
      if ($html_tag_restrictions === []) {
        throw new \InvalidArgumentException(sprintf('The value for the "%s" HTML tag is an empty array. This is not permitted, specify FALSE instead to indicate no attributes are allowed. Otherwise, list allowed attributes.', $html_tag_name));
      }
    }
  }

  /**
   * Validates allowed elements — phase 3: HTML tag attribute restriction keys.
   *
   * @param array $elements
   *   The allowed elements.
   *
   * @throws \InvalidArgumentException
   */
  private static function validateAllowedRestrictionsPhase3(array $elements): void {
    foreach($elements as $html_tag_name => $html_tag_restrictions) {
      if (!is_array($html_tag_restrictions)) {
        continue;
      }
      if (!Inspector::assertAllStrings(array_keys($html_tag_restrictions))) {
        throw new \InvalidArgumentException(sprintf('The "%s" HTML tag has attribute restrictions, but it is not an array of key-value pairs, with HTML tag attribute names as keys.', $html_tag_name));
      }

      foreach ($html_tag_restrictions as $html_tag_attribute_name => $html_tag_attribute_restrictions) {
        if (trim($html_tag_attribute_name) !== $html_tag_attribute_name) {
          throw new \InvalidArgumentException(sprintf('The "%s" HTML tag has an attribute restriction "%s" which contains whitespace. Omit the whitespace.', $html_tag_name, $html_tag_attribute_name));
        }
      }
    }
  }

  /**
   * Validates allowed elements — phase 4: HTML tag attr restriction values.
   *
   * @param array $elements
   *   The allowed elements.
   *
   * @throws \InvalidArgumentException
   */
  private static function validateAllowedRestrictionsPhase4(array $elements): void {
    foreach($elements as $html_tag_name => $html_tag_restrictions) {
      if (!is_array($html_tag_restrictions)) {
        continue;
      }

      foreach ($html_tag_restrictions as $html_tag_attribute_name => $html_tag_attribute_restrictions) {
        // The value must be either TRUE (meaning all values for this
        // are allowed), or an array of allowed attribute values.
        if ($html_tag_attribute_restrictions === TRUE) {
          continue;
        }
        if (!is_array($html_tag_attribute_restrictions)) {
          throw new \InvalidArgumentException(sprintf('The "%s" HTML tag has an attribute restriction "%s" which is neither TRUE nor an array of attribute value restrictions.', $html_tag_name, $html_tag_attribute_name));
        }
        if ($html_tag_attribute_restrictions === []) {
          throw new \InvalidArgumentException(sprintf('The "%s" HTML tag has an attribute restriction "%s" which is set to the empty array. This is not permitted, specify either TRUE to allow all attribute values, or list the attribute value restrictions.', $html_tag_name, $html_tag_attribute_name));
        }
        if (!Inspector::assertAll(function ($v) { return $v === TRUE; }, $html_tag_attribute_restrictions)) {
          throw new \InvalidArgumentException(sprintf('The "%s" HTML tag has attribute restriction "%s", but it is not an array of key-value pairs, with HTML tag attribute values as keys and TRUE as values.', $html_tag_name, $html_tag_attribute_name));
        }
      }
    }
  }

  /**
   * Creates the empty set of HTML restrictions.
   *
   * @return \Drupal\ckeditor5\HTMLRestrictions
   */
  public static function emptySet(): HTMLRestrictions {
    return new static([]);
  }

  /**
   * {@inheritdoc}
   */
  public function count(): int {
    return count($this->elements);
  }

  /**
   * Constructs a set of HTML restrictions matching the given text format.
   *
   * @param \Drupal\filter\Plugin\FilterInterface $filter
   *   A filter plugin instance to construct a HTML restrictions object for.
   *
   * @return \Drupal\ckeditor5\HTMLRestrictions
   */
  public static function fromFilterPluginInstance(FilterInterface $filter): HTMLRestrictions {
    return static::fromObjectWithHtmlRestrictions($filter);
  }

  /**
   * Constructs a set of HTML restrictions matching the given text format.
   *
   * @param \Drupal\filter\FilterFormatInterface $text_format
   *   A text format to construct a HTML restrictions object for.
   *
   * @return \Drupal\ckeditor5\HTMLRestrictions
   */
  public static function fromTextFormat(FilterFormatInterface $text_format): HTMLRestrictions {
    return static::fromObjectWithHtmlRestrictions($text_format);
  }

  private static function fromObjectWithHtmlRestrictions(object $object): HTMLRestrictions {
    if (!method_exists($object, 'getHTMLRestrictions')) {
      throw new \InvalidArgumentException();
    }

    $restrictions = $object->getHTMLRestrictions();
    if (!isset($restrictions['allowed'])) {
      // @todo Handle HTML restrictor filters that only set forbidden_tags
      //   https://www.drupal.org/project/ckeditor5/issues/3231336.
      throw new \DomainException('text formats with only filters that forbid tags rather than allowing tags are not yet supported.');
    }

    $allowed = $restrictions['allowed'];
    // @todo Validate attributes allowed or forbidden on all elements
    //   https://www.drupal.org/project/ckeditor5/issues/3231334.
    if (isset($allowed['*'])) {
      unset($allowed['*']);
    }

    return new static($allowed);
  }

  /**
   * @param $elements_string
   *   A string representing a list of allowed HTML elements.
   *
   * @return \Drupal\ckeditor5\HTMLRestrictions
   */
  public static function parse($elements_string): HTMLRestrictions {
    if (is_array($elements_string)) {
      $elements_string = implode(' ', $elements_string);
    }

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
          self::providedElementsAttributes($elements, $tag, $attribute_name, $value);
        }
      }
      else {
        if (!isset($elements[$tag])) {
          $elements[$tag] = FALSE;
        }
      }
    }

    return new static($elements);
  }

  /**
   * Compares two HTML restrictions.
   *
   * @param \Drupal\ckeditor5\HTMLRestrictions $other
   *   The HTML restrictions to compare to.
   *
   * @return \Drupal\ckeditor5\HTMLRestrictions
   *   Returns a new HTML restrictions value object with all the elements that
   *   are not present in $other.
   */
  public function diff(HTMLRestrictions $other): HTMLRestrictions {
    $diff_elements = array_filter(
      DiffArray::diffAssocRecursive($this->elements, $other->elements),
      // DiffArray::diffAssocRecursive() does not know the semantics of the HTML
      // restrictions array: unaware that `TAG => FALSE` is a subset of
      // `TAG => foo` and that in turn is a subset of `TAG => TRUE`.
      // @see \Drupal\filter\Entity\FilterFormat::getHtmlRestrictions()
      function ($value, string $tag) use ($other) {
        return $value !== FALSE || !array_key_exists($tag, $other->elements);
      },
      ARRAY_FILTER_USE_BOTH
    );

    return new static($diff_elements);
  }

  public function intersect(HTMLRestrictions $other): HTMLRestrictions {
    $intersection_based_on_tags = array_intersect_key($this->elements, $other->elements);
    $intersection = [];
    foreach (array_keys($intersection_based_on_tags) as $tag) {
      // If either does not allow attributes, neither does the intersection.
      if ($this->elements[$tag] === FALSE || $other->elements[$tag] === FALSE) {
        $intersection[$tag] = FALSE;
      }
      // If both allow all attributes, so does the intersection.
      elseif ($this->elements[$tag] === TRUE && $other->elements[$tag] === TRUE) {
        $intersection[$tag] = TRUE;
      }
      // If the first allows all attributes, return the second.
      elseif ($this->elements[$tag] === TRUE) {
        $intersection[$tag] = $other->elements[$tag];
      }
      // And vice versa.
      elseif ($other->elements[$tag] === TRUE) {
        $intersection[$tag] = $this->elements[$tag];
      }
      // In all other cases, we need to return the most restrictive
      // intersection of per-attribute restrictions.
      else {
        $intersection[$tag] = [];

        $attributes_intersection = array_intersect_key($this->elements[$tag], $other->elements[$tag]);
        foreach (array_keys($attributes_intersection) as $attr) {
          // If either does not allow this attribute, neither does the intersection.
          if ($this->elements[$tag][$attr] === FALSE || $other->elements[$tag][$attr] === FALSE) {
            $intersection[$tag][$attr] = FALSE;
          }
          // If both allow all attribute values, so does the intersection.
          elseif ($this->elements[$tag][$attr] === TRUE && $other->elements[$tag][$attr] === TRUE) {
            $intersection[$tag][$attr] = TRUE;
          }
          // If the first allows all attribute values, return the second.
          elseif ($this->elements[$tag][$attr] === TRUE) {
            $intersection[$tag][$attr] = $other->elements[$tag][$attr];
          }
          // And vice versa.
          elseif ($other->elements[$tag][$attr] === TRUE) {
            $intersection[$tag][$attr] = $this->elements[$tag][$attr];
          }
          else {
            $intersection[$tag][$attr] = array_intersect($this->elements[$tag][$attr], $other->elements[$tag][$attr]);
          }
        }
      }
    }

    return new static($intersection);
  }

  public function union(HTMLRestrictions $other): HTMLRestrictions {
    $union = array_merge_recursive($this->elements, $other->elements);
    // When recursively merging elements arrays, unkeyed boolean values can
    // appear in attribute config arrays. This removes them.
    foreach ($union as $tag => $tag_config) {
      if (is_array($tag_config)) {
        $union[$tag] = array_filter($tag_config);
      }
    }
    return new static($union);
  }

  /**
   * Gets allowed elements, optionally with wildcards processed.
   *
   * @param bool $retain_wildcard
   *   Whether to retain the wildcard or not.
   *
   * @return array
   *
   * @see \Drupal\filter\Plugin\FilterInterface::getHTMLRestrictions
   */
  public function getAllowedElements(bool $retain_wildcard = FALSE): array {
    $elements = $this->elements;
    // @todo move this to another helper method which returns a new value object with everything processed
    foreach ($elements as $tag_name => $tag_config) {
      if (substr($tag_name, 0, 1) === '$') {
        $wildcard_tags = self::getWildcardTags($tag_name);
        foreach ($wildcard_tags as $wildcard_tag) {

          if (isset($elements[$wildcard_tag])) {
            foreach ($tag_config as $attribute_name => $attribute_value) {
              if (is_array($attribute_value)) {
                $attribute_value = array_keys($attribute_value);
              }
              $element_already_allows_all_values = isset($elements[$wildcard_tag][$attribute_name]) && $elements[$wildcard_tag][$attribute_name] === TRUE;
              if (!$element_already_allows_all_values) {
                self::providedElementsAttributes($elements, $wildcard_tag, $attribute_name, $attribute_value);
              }
            }
          }
        }
        if (!$retain_wildcard) {
          unset($elements[$tag_name]);
        }
      }
    }
    return $elements;
  }

  public function toCKEditor5ElementsArray(): array {
    $readable = [];
    foreach ($this->elements as $tag => $attributes) {
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

  public function toFilterHtmlAllowedTagsString(): string {
    return implode(' ', $this->toCKEditor5ElementsArray());
  }

  /**
   * Gets the HTML restrictions.
   *
   * @return string[]
   *   An array of allowed elements, structured in the manner expected by the
   *   CKEditor 5 htmlSupport plugin constructor.
   *
   * @see https://ckeditor5.github.io/docs/nightly/ckeditor5/latest/features/general-html-support.html#configuration
   */
  public function toGeneralHtmlSupportConfig(): array {
    $allowed = [];
    foreach ($this->elements as $tag => $attributes) {
      $to_allow['name'] = $tag;
      assert($attributes === FALSE || is_array($attributes));
      if (is_array($attributes)) {
        foreach ($attributes as $name => $value) {
          assert($value === TRUE || Inspector::assertAllStrings($value));
          $to_allow['attributes'][$name] = $value;
        }
      }
      $allowed[] = $to_allow;
    }

    return $allowed;
  }

  /**
   * Gets a list of block-level elements.
   *
   * @return string[]
   *   An array of block-level element tags.
   */
  private static function getBlockElementList(): array {
    return array_filter(array_keys(Elements::$html5), function (string $element): bool {
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
  protected static function getWildcardTags(string $wildcard): array {
    $wildcard_element_method = self::WILDCARD_ELEMENT_METHODS[$wildcard];
    return call_user_func([self::class, $wildcard_element_method]);
  }

  /**
   * Adds allowed attributes to the elements array.
   *
   * @param array $elements
   *   The elements array.
   * @param string $tag
   *   The tag having its attributes configured.
   * @param string $attribute
   *   The attribute being configured.
   * @param array|bool $value
   *   The attribute config value.
   */
  protected static function providedElementsAttributes(array &$elements, string $tag, string $attribute, $value) : void {
    $attribute_already_allows_all = isset($elements[$tag][$attribute]) && $elements[$tag][$attribute] === TRUE;

    if ($value === TRUE) {
      $elements[$tag][$attribute] = TRUE;
    }
    elseif (!$attribute_already_allows_all) {
      foreach ($value as $attribute_value) {
        $elements[$tag][$attribute][$attribute_value] = TRUE;
      }
    }
  }

}
