<?php

namespace Drupal\jqueryui_dialog_test\Controller;

use Drupal\Core\Controller\ControllerBase;

// cSpell:ignore qunit

/**
 * Reproduces the HTML used by jQuery UI dialog qunit tests.
 */
class JqueryUiDialogTestController extends ControllerBase {

  /**
   * The HTML for testing jQuery UI dialog.
   *
   * @return array
   *   A render array
   */
  public function build() {
    return [
      'container' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'dialog-container',
        ],
        'dialog1' => [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'dialog1',
          ],
        ],
        'dialog2' => [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'dialog2',
          ],
        ],
        'form-dialog' => [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'form-dialog',
            'title' => 'Profile Information',
          ],
          'spacer' => [
            '#type' => 'container',
            '#attributes' => [
              'style' => 'height: 250px;',
            ],
          ],
          'fieldset' => [
            '#type' => 'fieldset',
            '#title' => 'Please share some personal information',
            'animal' => [
              '#type' => 'textfield',
              '#id' => 'favorite-animal',
              '#title' => 'Your favorite animal',
            ],
            'color' => [
              '#type' => 'textfield',
              '#id' => 'favorite-color',
              '#title' => 'Your favorite color',
            ],
          ],
          'group' => [
            '#type' => 'container',
            '#attributes' => [
              'role' => 'group',
              'aria-describedby' => 'section2',
            ],
            'more' => [
              '#type' => 'html_tag',
              '#tag' => 'p',
              '#value' => 'Some more (optional) information',
              '#id' => 'section2',
            ],
            'food' => [
              '#type' => 'textfield',
              '#id' => 'favorite-food',
              '#title' => 'Favorite food',
            ],
          ],
        ],
      ],
      'wrap1' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['wrap'],
          'id' => 'wrap1',
        ],
      ],
      'wrap2' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['wrap'],
          'id' => 'wrap2',
        ],
      ],
      '#attached' => [
        'library' => [
          'core/jquery',
          'core/jquery.ui.dialog',
          'jquery_simulate/jquery.simulate',
        ],
      ],
    ];
  }

}
