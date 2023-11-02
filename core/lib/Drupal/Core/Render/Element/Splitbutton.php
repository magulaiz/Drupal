<?php

namespace Drupal\Core\Render\Element;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a button that toggles the visibility of list of actions.
 *
 * There is an optional "primary" action that is always visible, and not part
 * of the toggleable list.
 *
 * Properties:
 * - #splitbutton_items: Items that will be themed as a splitbutton_item_list.
 *   By default, the items can be of the following types: submit, link and
 *   button. All other elements will be filtered out. Additional render elements
 *   can be "allowed" in by using the #splitbutton_additional_allowed_elements
 *   attribute.
 * - #splitbutton_additional_allowed_elements: An array of strings, each of
 *   which is a render element type that should be allowed in the Splitbutton
 *   beyond the default types of submit, link, and button. Two important things
 *   to keep in mind:
 *     - When using elements outside of the default types, custom styling might
 *      be needed as not all elements have provided splitbutton styles.
 *     - Form elements inside a splitbutton will not be part of the form state.
 * - #splitbutton_type: A string or an array or strings defining a type of
 *   dropbutton variant for styling purposes.
 * - #title: This changes the default splitbutton behavior of displaying a
 *   primary splitbutton item next a separate toggle button. When this property
 *   is present, there is no primary item, just a toggle.
 * - #exclude_toggle: Defaults to FALSE. Largely used by render elements
 *   extending splitbutton. When TRUE, no toggle button is added even if the
 *   configuration would typically result in its addition. For these uses, it
 *   should be confirmed that there is still an element with the
 *   `data-drupal-splitbutton-trigger` attribute, as it is necessary for
 *   splitbutton's JavaScript.
 *
 * Deprecated Properties:
 * - #links: An array of links to actions. See template_preprocess_links() for
 *   documentation the properties of links in this array. This property exists
 *   so dropbuttons can easily be converted to splitbuttons. New splitbuttons
 *   should not use this property, and it will be removed in Drupal 10.
 * - #dropbutton_type: The value is copied or appended to #splitbutton_type.
 *   This will be removed in Drupal 10.
 *
 * Usage Example:
 * @code
 * $form['actions']['splitbutton_actions'] = [
 *   '#type' => 'splitbutton',
 *   '#splitbutton_type' => 'small',
 *   '#splitbutton_items' => [
 *     'added_link' => [
 *       '#type' => 'link',
 *       '#title' => $this->t('Simple Form'),
 *       '#url' => Url::fromRoute('route.for.the_link'),
 *     ],
 *     'added_button' => [
 *       '#type' => 'button',
 *       '#value' => $this->t('Added Button'),
 *     ],
 *     'another_added_button' => [
 *       '#type' => 'submit',
 *       '#value' => $this->t('Another Added Button'),
 *     ],
 *   ],
 * ];
 * @endcode
 *
 * @RenderElement("splitbutton")
 */
class Splitbutton extends FormElement {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [
        [$class, 'preRenderSplitbutton'],
      ],
      '#theme_wrappers' => ['splitbutton'],
      '#attached' => [
        'library' => 'core/drupal.splitbutton',
      ],
    ];
  }

  /**
   * Pre-render callback. Builds splitbutton render array.
   */
  public static function preRenderSplitbutton(array $element) {
    $element['#variants'] = [];

    if (!empty($element['#splitbutton_type'])) {
      // If #splitbutton_type exists and it is a string, place it in an array.
      $element['#variants'] = is_array($element['#splitbutton_type']) ? $element['#splitbutton_type'] : [$element['#splitbutton_type']];
    }

    if (!empty($element['#dropbutton_type'])) {
      @trigger_error('Splitbutton using #dropbutton_type is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3169786', E_USER_DEPRECATED);
      $element['#variants'][] = $element['#dropbutton_type'];
    }

    $items = static::collectItems($element);
    static::buildToggleAttributes($element);

    // If the #title property is not present, splitbutton takes the first item
    // from the $items array and makes that the main "button". A dedicated
    // toggle button is also provided in these instances.
    if (!isset($element['#title'])) {
      $first_item = array_shift($items);
      $element['#main_element'] = $first_item;
      // All element types supported by default have either #title or #value. It
      // is possible to allow other element types, but splitbutton will expect
      // one of these two properties to be present in the first item.
      assert(isset($first_item['#title']) || $first_item['#value'], 'The first item in a splitbutton must be an element with either a #title or #value property');
      $title = $first_item['#title'] ?? $first_item['#value'];
      $element['#toggle_attributes']['aria-label'] = t('List additional actions beyond @title', ['@title' => $title]);
    }

    // If additional items are present, place them in a splitbutton list.
    if (count($items)) {
      $element['#additional_elements'] = $items;
      $element['#splitbutton_multiple'] = TRUE;
      $element['#attributes']['data-drupal-splitbutton-multiple'] = '';
    }
    else {
      $element['#splitbutton_multiple'] = FALSE;
      $element['#attributes']['data-drupal-splitbutton-single'] = '';
    }

    if (!empty($element['#variants'])) {
      $element['#attributes']['data-drupal-splitbutton-variants'] = implode(',', $element['#variants']);
    }

    return $element;
  }

  /**
   * Collects items that will be added to the splitbutton.
   *
   * @param array $element
   *   The render element.
   *
   * @return array
   *   An array of splitbutton list items.
   */
  public static function collectItems(array $element) {
    $items = $element['#splitbutton_items'] ?? [];

    // The #links property was used by dropbuttons. To facilitate an easier
    // switch from dropbutton to splitbutton, items in a #links array are
    // converted to `link` render elements with a #type, and added to the list
    // of splitbutton elements.
    if (isset($element['#links'])) {
      @trigger_error("Splitbutton using #links is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3169786", E_USER_DEPRECATED);
      foreach ($element['#links'] as &$op) {
        if (isset($op['url']) && isset($op['title'])) {
          $op['#url'] = $op['url'];
          unset($op['url']);
          $op['#title'] = $op['title'];
          unset($op['title']);
          $op['#type'] = 'link';
        }
      }
      $items += $element['#links'];
    }

    $additional_allowed_elements = $element['#splitbutton_additional_allowed_elements'] ?? [];
    static::filterItems($items, $additional_allowed_elements);
    return $items;
  }

  /**
   * Adds attributes used for the toggle button.
   *
   * @param array $element
   *   The render element.
   */
  public static function buildToggleAttributes(array &$element) {
    $element['#toggle_attributes'] = [
      'type' => 'button',
      'aria-haspopup' => 'true',
      'aria-expanded' => 'false',
      'data-drupal-selector' => 'splitbutton-trigger',
    ];
  }

  /**
   * Checks for unsupported element types in a splitbutton item list.
   *
   * @param array $items
   *   The splitbutton list items.
   * @param string[] $additional_allowed_types
   *   An array of other allowed element types.
   */
  public static function filterItems(array $items, $additional_allowed_types = []) {
    // Only submit, button, and link are guaranteed to present well within a
    // splitbutton, but other element types should work fine but may require
    // some custom theming to look right.
    $allowed_types = ['submit', 'button', 'link', ...$additional_allowed_types];
    foreach ($items as $item) {
      if (!isset($item['#type']) || !in_array($item['#type'], $allowed_types)) {
        throw new \LogicException('Splitbutton item is either missing #type, or #type is not submit, button or link.');
      }
    }
  }

}
