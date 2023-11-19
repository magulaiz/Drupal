<?php

namespace Drupal\Core\Render\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;

/**
 * Provides a search element for filtering a list with JavaScript.
 *
 * - #list_container_id: (optional) The CSS ID of the container to search in. If
 *   omitted, defaults to 'filter-container'.
 * - #list_item: (optional) The CSS selector, relative to the container, for the
 *   items to search for. If omitted, defaults to '.filter-item'.
 * - #list_text: (optional) The CSS selector, relative to a list item, of
 *   elements containing text to search for. This may produce multiple elements.
 *   Defaults to an empty string, which indicates that the whole of the list
 *   item should be considered searchable text.
 * - #list_group: (optional) The CSS selector, relative to the container, for
 *   the groups of items. If omitted, the list is not considered to have
 *   grouping.
 * - #grouping_method: (optional) The name of a method on Drupal.listFilter to
 *   use to associate items into groups. The base library supports the
 *   following values:
 *   - getRowGroupUsingContainment: Item elements are within their group
 *     elements.
 *   - getRowGroupUsingPriorSibling: Item elements are siblings of group
 *     elements. The group of an item is its first prior sibling element that is
 *     a group.
 * - #announce: (optional) An array of strings to use for accessibility ARIA
 *   announcements when the number of visible items is changed. The keys are:
 *   - singular: Message to announce when only one item is visible.
 *   - plural: Message to announce when more than one items are visible. This
 *     must contain the '@count' placeholder.
 *   - all': Message to announce when all items are visible.
 * - #debug: (optional) Set to TRUE to add CSS styling to highlight the
 *   different elements.
 *
 * @RenderElement("list_filter")
 */
class ListFilter extends Search {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#pre_render' => [
        [$class, 'preRenderListFilterSearch'],
      ],
      '#theme' => 'input__search',
      '#theme_wrappers' => ['form_element'],
      '#list_container_id' => 'filter-container',
      '#list_item' => '.filter-item',
      '#list_text' => '',
      '#list_group' => '',
      '#grouping_method' => '',
      '#announce' => [
        'singular' => t('1 item is available in the modified list.'),
        'plural' => t('@count items are available in the modified list.'),
        'all' => t('All available items are listed.'),
      ],
      '#debug' => FALSE,
    ];
  }

  /**
   * Prepares a #type 'list_filter' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderListFilterSearch($element) {
    $element['#attributes']['type'] = 'search';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-search']);

    $list_filter_id = $element['#list_container_id'];

    // Ensure this element has a unique HTML ID.
    $search_field_id = Html::getUniqueId($element['#attributes']['id'] ?? $list_filter_id . '-search');
    $element['#attributes']['id'] = $search_field_id;

    $settings = [
      'search_field_id' => $search_field_id,
    ];

    foreach ([
      '#list_container_id',
      '#list_item',
      '#list_text',
      '#list_group',
      '#grouping_method',
      '#announce',
      '#debug',
    ] as $key) {
      $settings[substr($key, 1)] = $element[$key];
    }

    $element['#attached']['drupalSettings']['listFilter'][$list_filter_id] = $settings;

    $element['#attached']['library'][] = 'core/drupal.list-filter';

    return $element;
  }

}
