<?php

namespace Drupal\toolbar\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Element;

/**
 * Provides a render element for the default Drupal toolbar.
 *
 * @RenderElement("toolbar")
 */
class Toolbar extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#pre_render' => [
        [$class, 'preRenderToolbar'],
      ],
      '#theme' => 'toolbar',
      '#attached' => [
        'library' => [
          'toolbar/toolbar',
        ],
      ],
      // Metadata for the toolbar wrapping element.
      '#attributes' => [
        'id' => 'toolbar-administration',
        'role' => 'group',
        'aria-label' => $this->t('Site administration toolbar'),
      ],
      // Metadata for the administration bar.
      '#bar' => [
        '#heading' => $this->t('Toolbar items'),
        '#attributes' => [
          'id' => 'toolbar-bar',
          'role' => 'navigation',
          'aria-label' => $this->t('Toolbar items'),
        ],
      ],
    ];
  }

  /**
   * Builds the Toolbar as a structured array ready for rendering.
   *
   * Since building the toolbar takes some time, it is done just prior to
   * rendering to ensure that it is built only if it will be displayed.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   *
   * @see toolbar_page_top()
   */
  public static function preRenderToolbar($element) {
    // Get the configured breakpoints to switch from vertical to horizontal
    // toolbar presentation.
    $breakpoints = static::breakpointManager()->getBreakpointsByGroup('toolbar');
    if (!empty($breakpoints)) {
      $media_queries = [];
      foreach ($breakpoints as $id => $breakpoint) {
        $media_queries[$id] = $breakpoint->getMediaQuery();
      }

      $element['#attached']['drupalSettings']['toolbar']['breakpoints'] = $media_queries;
    }

    $module_handler = static::moduleHandler();
    // Get toolbar items from all modules that implement hook_toolbar().
    $items = $module_handler->invokeAll('toolbar');
    // Allow for altering of hook_toolbar().
    $module_handler->alter('toolbar', $items);
    // Sort the children.
    uasort($items, ['\Drupal\Component\Utility\SortArray', 'sortByWeightProperty']);

    // Merge in the original toolbar values.
    $element = array_merge($element, $items);

    // Assign each item a unique ID, based on its key.
    foreach (Element::children($element) as $key) {
      $element[$key]['#id'] = Html::getId('toolbar-item-' . $key);
    }

    $collapse_info = ['num_items' => 0];
    foreach (Element::children($element) as $key) {
      if (!isset($element[$key]['#type']) || $element[$key]['#type'] !== 'toolbar_item') {
        continue;
      }

      // If a tab that opens a tray is in the items, reset the count as it can't
      // be included in a collapsed element.
      if ($collapse_info['num_items'] > 1 && isset($element[$key]['tray'])) {
        $collapse_info['start'] = NULL;
        $collapse_info['end'] = NULL;
      }

      if ($key !== 'home' && !isset($element[$key]['tray'])) {
        if (!isset($collapse_info['start'])) {
          $collapse_info['start'] = $key;
        }
        else {
          $collapse_info['end'] = $key;
        }
        $collapse_info['num_items'] += 1;
      }
    }

    if ($collapse_info['num_items'] > 2) {
      $go = FALSE;
      foreach (Element::children($element) as $key) {
        if ($key === $collapse_info['start']) {
          $go = TRUE;
        }

        if ($go && isset($element[$key]['#type']) && $element[$key]['#type'] === 'toolbar_item') {
          $element[$key]['#wrapper_attributes']['data-drupal-selector'] = 'toolbar-extra-item';
        }

        if ($key === $collapse_info['end']) {
          $go = FALSE;
        }
      }

      $element['extra_item_toggle'] = [
        '#type' => 'toolbar_item',
        '#wrapper_attributes' => [
          'data-drupal-selector' => 'toolbar-extra-item-toggle',
        ],
        '#id' => 'extra-item-toggle',
        'tab' => [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => t('...'),
          '#attributes' => [
            'type' => 'button',
            '#aria-label' => t('More toolbar items'),
            'data-toolbar-extra-item-toggle-button' => TRUE,
          ],
        ],
      ];
    }

    return $element;
  }

  /**
   * Wraps the breakpoint manager.
   *
   * @return \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected static function breakpointManager() {
    return \Drupal::service('breakpoint.manager');
  }

  /**
   * Wraps the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected static function moduleHandler() {
    return \Drupal::moduleHandler();
  }

}
