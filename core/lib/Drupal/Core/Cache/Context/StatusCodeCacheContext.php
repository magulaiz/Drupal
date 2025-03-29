<?php

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines the StatusCodeCacheContext service, for "per status code" caching.
 *
 * Cache context ID: 'status_code'.
 */
class StatusCodeCacheContext extends RequestStackCacheContextBase {

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): \Stringable {
    return t('Status code');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): string {
    $exception = $this->requestStack->getCurrentRequest()->attributes->get('exception');
    if ($exception) {
      return (string) $exception->getStatusCode();
    }
    return '200';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return new CacheableMetadata();
  }

}
