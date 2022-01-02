<?php

namespace Drupal\dialog_auto_buttons_test\Controller;

use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;

/**
 * Test controller display modal links and content.
 */
class TestController {

  /**
   * Displays test links that will open in the modal dialog.
   *
   * @return array
   *   Render array with links.
   */
  public function linksDisplay() {

    return [
      'default' => [
        '#title' => 'Default!',
        '#type' => 'link',
        '#url' => Url::fromRoute('dialog_auto_buttons_test.modal_content'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
        ],
        '#attached' => [
          'library' => [
            'core/drupal.ajax',
          ],
        ],
      ],
      'auto_buttons_false' => [
        '#title' => 'Set to false!',
        '#type' => 'link',
        '#url' => Url::fromRoute('dialog_auto_buttons_test.modal_content'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-options' => Json::encode([
            'drupalAutoButtons' => FALSE,
          ]),
        ],
        '#attached' => [
          'library' => [
            'core/drupal.ajax',
          ],
        ],
      ],
      'auto_buttons_true' => [
        '#title' => 'Set to true!',
        '#type' => 'link',
        '#url' => Url::fromRoute('dialog_auto_buttons_test.modal_content'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-options' => Json::encode([
            'drupalAutoButtons' => TRUE,
          ]),
        ],
        '#attached' => [
          'library' => [
            'core/drupal.ajax',
          ],
        ],
      ],
    ];
  }

}
