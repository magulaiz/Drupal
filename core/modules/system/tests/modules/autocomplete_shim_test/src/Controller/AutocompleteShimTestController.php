<?php

namespace Drupal\autocomplete_shim_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * For testing the jQuery UI autocomplete shim.
 */
class AutocompleteShimTestController extends ControllerBase {

  /**
   * Provides a page that loads A11y autocomplete, but all inputs use jQuery.
   *
   * @return array
   *   The render array.
   */
  public function bypassA11y() {
    return [
      'container1' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'autocomplete-wrap1',
          'class' => ['autocomplete-wrap'],
        ],
      ],
      'container2' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'autocomplete-wrap2',
          'class' => ['autocomplete-wrap'],
        ],
        'input' => [
          '#type' => 'html_tag',
          '#tag' => 'input',
          '#attributes' => [
            'id' => 'autocomplete',
            'class' => ['foo'],
          ],
        ],
      ],
      'container_contenteditable' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'autocomplete-contenteditable',
          'tabindex' => 0,
          'contenteditable' => '',
        ],
      ],
      'textarea' => [
        '#type' => 'html_tag',
        '#tag' => 'textarea',
        '#attributes' => [
          'id' => ['autocomplete-textarea'],
        ],
      ],
      '#attached' => [
        'library' => [
          'core/drupal.autocomplete',
          'core/jquery.ui.autocomplete',
          // Attach jquery.simulate for use by Nightwatch tests.
          'jquery_simulate/jquery.simulate',
        ],
      ],
    ];
  }

  /**
   * Provides a page with the shimmed jQuery UI autocomplete library.
   *
   * @return array
   *   The render array.
   */
  public function build() {
    $build = $this->bypassA11y();
    $build['container2']['input']['#attributes']['class'][] = 'form-autocomplete';
    $build['container_contenteditable']['#attributes']['class'][] = 'form-autocomplete';
    $build['textarea']['#attributes']['class'][] = 'form-autocomplete';
    $build['#attached']['library'] = [
      'core/drupal.autocomplete',
      // Attach jquery.simulate for use by Nightwatch tests.
      'jquery_simulate/jquery.simulate',
    ];
    return $build;
  }

  /**
   * The test form with an added input that directly calls jQuery autocomplete.
   *
   * @return array
   *   The render array.
   */
  public function buildWithAdditionalDirectJquery() {
    $build = $this->build();
    $build['direct_jquery_input'] = [
      '#type' => 'html_tag',
      '#tag' => 'input',
      '#attributes' => [
        'id' => 'direct-jquery',
      ],
    ];

    return $build;
  }

}
