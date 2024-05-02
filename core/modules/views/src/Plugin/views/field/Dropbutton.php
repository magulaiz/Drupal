<?php

namespace Drupal\views\Plugin\views\field;

use Drupal\Core\Render\Builder\DropbuttonBuilder;
use Drupal\views\ResultRow;

/**
 * Provides a handler that renders links as dropbutton.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dropbutton")
 */
class Dropbutton extends Links {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $links = $this->getLinks();

    if (!empty($links)) {
      return DropbuttonBuilder::create()
        ->links($links)
        ->toRenderable();
    }
    else {
      return '';
    }
  }

}
