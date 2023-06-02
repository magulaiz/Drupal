<?php

namespace Drupal\Core\Form;

use Drupal\Core\Render\Element;

/**
 * Provides common functionality for form elements.
 */
class FormElementHelper {

  /**
   * Retrieves a form element.
   *
   * @param string $name
   *   The name of the form element. If the #parents property of your form
   *   element is ['foo', 'bar', 'baz'] then the name is 'foo][bar][baz'.
   * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @return array
   *   The form element.
   */
  public static function getElementByName($name, array $form) {
    foreach (Element::children($form) as $key) {
      if (implode('][', $form[$key]['#parents']) === $name) {
        return $form[$key];
      }
      elseif ($element = static::getElementByName($name, $form[$key])) {
        return $element;
      }
    }
    return [];
  }

  /**
   * Returns the title for the element.
   *
   * If the element has no title, this will recurse through all children of the
   * element until a title is found.
   *
   * @param array $element
   *   An associative array containing the properties of the form element.
   *
   * @return string
   *   The title of the element, or an empty string if none is found.
   */
  public static function getElementTitle(array $element) {
    $title = '';
    if (isset($element['#title'])) {
      $title = $element['#title'];
    }
    else {
      foreach (Element::children($element) as $key) {
        if ($title = static::getElementTitle($element[$key])) {
          break;
        }
      }
    }
    return $title;
  }

  /**
   * Helper function to extract form options.
   *
   * @param array|\Countable|string|null $options
   *   Provided options, might be an enum.
   *
   * @return array|\Countable|null
   *   The extracted options.
   */
  public static function extractOptions(null|array|\Countable|string $options): null|array|\Countable {
    if (is_string($options) && enum_exists($options)) {
      $cases = $options::cases();
      return array_combine(
        array_map(fn(\UnitEnum $enum) => $enum->name, $cases),
        array_map(fn(\UnitEnum $enum) => $enum->value, $cases),
      );
    }
    return $options;
  }

}
