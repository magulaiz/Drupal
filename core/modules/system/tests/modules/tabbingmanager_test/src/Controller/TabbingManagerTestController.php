<?php

namespace Drupal\tabbingmanager_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * For testing the Tabbing Manager.
 */
class TabbingManagerTestController extends ControllerBase {

  /**
   * Provides a page with the tabbingManager library for testing tabbing manager.
   *
   * @return array
   *   The render array.
   */
  public function build() {
    return [
      'container' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'tabbingmanager-test-container',
        ],
        'first' => [
          '#type' => 'text',
          '#title' => $this->t('First'),
          '#attributes' => [
            'id' => 'first',
          ],
        ],
        'second' => [
          '#type' => 'text',
          '#title' => $this->t('Second'),
          '#attributes' => [
            'id' => 'second',
          ],
        ],
        'third' => [
          '#type' => 'text',
          '#title' => $this->t('Third'),
          '#attributes' => [
            'id' => 'third',
          ],
        ],
      ],
      'another_container' => [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'tabbingmanager-test-another-container',
        ],
        'fourth' => [
          '#type' => 'text',
          '#title' => $this->t('Fourth'),
          '#attributes' => [
            'id' => 'fourth',
          ],
        ],
        'fifth' => [
          '#type' => 'text',
          '#title' => $this->t('Fifth'),
          '#attributes' => [
            'id' => 'fifth',
          ],
        ],
        'sixth' => [
          '#type' => 'text',
          '#title' => $this->t('Sixth'),
          '#attributes' => [
            'id' => 'sixth',
          ],
        ],
      ],
      '#attached' => ['library' => ['core/drupal.tabbingmanager']],

    ];
  }

}
