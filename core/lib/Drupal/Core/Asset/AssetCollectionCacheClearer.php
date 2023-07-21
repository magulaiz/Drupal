<?php

namespace Drupal\Core\Asset;

use Drupal\Core\Cache\CacheClearerInterface;

class AssetCollectionCacheClearer implements CacheClearerInterface {

  public function __construct(
    protected AssetCollectionOptimizerInterface $cssOptimizer,
    protected AssetCollectionOptimizerInterface $jsOptimizer,
    protected AssetQueryStringInterface $assetQueryString,
  ) {}

  public function clearCache(): void {
    $this->cssOptimizer->deleteAll();
    $this->jsOptimizer->deleteAll();
    $this->assetQueryString->reset();
  }

}
