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
    ] as $key) {
      $settings[substr($key, 1)] = $element[$key];
    }

    $element['#attached']['drupalSettings']['listFilter'][$list_filter_id] = $settings;

    $element['#attached']['library'][] = 'core/drupal.list-filter';

    return $element;
  }

}
