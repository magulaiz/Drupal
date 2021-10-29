<?php

namespace Drupal\autocomplete_shim_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * For testing the jQuery UI autocomplete shim.
 */
class AutocompleteShimTestController extends ControllerBase {

  /**
   * Provides a page with the shimmed jQuery UI autocomplete library.
   *
   * @return array
   *   The render array.
   */
  public function build() {
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
            'data-autocomplete-path' => TRUE,
          ],
        ],
      ],
      'container_contenteditable' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'autocomplete-contenteditable',
          'tabindex' => 0,
          'contenteditable' => '',
          'data-autocomplete-path' => TRUE,
        ],
      ],
      'textarea' => [
        '#type' => 'html_tag',
        '#tag' => 'textarea',
        '#attributes' => [
          'id' => ['autocomplete-textarea'],
          'data-autocomplete-path' => TRUE,
        ],
      ],
      '#attached' => [
        'library' => [
          'core/drupal.autocomplete',
          // Attach jquery.simulate for use by Nightwatch tests.
          'jquery_simulate/jquery.simulate',
        ],
      ],
    ];
  }

}
