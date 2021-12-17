<?php

namespace Drupal\layout_builder_tempstore_access_test\Controller;

use Drupal\layout_builder\SectionStorageInterface;

/**
 * Controller that tells you how many sections are within a section storage.
 */
class SectionCountController {

  /**
   * Displays the total number of sections for the supplied section storage.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   *
   * @return array
   *   The render array.
   */
  public function build(SectionStorageInterface $section_storage) {
    return [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $section_storage->count(),
      '#attributes' => [
        'id' => 'layout-builder-tempstore-access-test-section-count',
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
