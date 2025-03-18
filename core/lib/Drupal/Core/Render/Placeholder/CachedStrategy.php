<?php

declare(strict_types=1);

namespace Drupal\Core\Render\Placeholder;

use Drupal\Core\Render\RenderCacheInterface;

/**
 * Looks up placeholders in the render cache and returns those we could find.
 */
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
    $return = $this->renderCache->getMultiple($placeholders);

    foreach ($return as $key => $placeholder) {
      if (!empty($placeholder['#attached']['placeholders'])) {
        $cached = $this->renderCache->getMultiple($placeholder['#attached']['placeholders']);
        foreach ($cached as $cache_key => $value) {
          $return[$key]['#attached']['placeholders'][$cache_key] = $value;
        }
      }
    }

    return $return;
  }

}
