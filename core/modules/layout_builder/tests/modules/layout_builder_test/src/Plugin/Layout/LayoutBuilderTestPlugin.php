<?php

namespace Drupal\layout_builder_test\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * @Layout(
 *   id = "layout_builder_test_plugin",
 *   label = @Translation("Layout Builder Test Plugin"),
 *   regions = {
 *     "main" = {
 *       "label" = @Translation("Main Region")
 *     }
 *   },
 * )
 */
class LayoutBuilderTestPlugin extends LayoutDefault {

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $regions['main']['#attributes']['class'][] = 'go-birds';
    if ($this->inPreview) {
      $regions['main']['#attributes']['class'][] = 'go-birds-preview';
    }
    return parent::build($regions);
  }

}
