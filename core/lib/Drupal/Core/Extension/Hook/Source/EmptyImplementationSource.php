<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\Source;

/**
 * Empty source..
 */
class EmptyImplementationSource implements ImplementationSourceInterface {

  /**
   * {@inheritdoc}
   */
  public function getImplementations(): array {
    return [];
  }

}
