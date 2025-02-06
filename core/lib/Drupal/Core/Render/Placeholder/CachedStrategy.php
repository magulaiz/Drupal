<?php

declare(strict_types=1);

namespace Drupal\Core\Render\Placeholder;

use Drupal\Core\Render\RenderCacheInterface;

class CachedStrategy implements PlaceholderStrategyInterface {

  public function __construct(
    protected readonly PlaceholderStrategyInterface $placeholderStrategy,
    protected readonly RenderCacheInterface $renderCache,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function processPlaceholders(array $placeholders) {
    $new_placeholders = [];

    $cached_items = $this->renderCache->getMultiple($placeholders);

    foreach ($cached_items as $placeholder => $elements) {
      // Recursively process placeholders
      if (!empty($elements['#attached']['placeholders'])) {
        $elements['#attached']['placeholders'] = $this->placeholderStrategy->processPlaceholders($elements['#attached']['placeholders']);
      }

      $new_placeholders[$placeholder] = $elements;
    }

    return $new_placeholders;
  }

}
