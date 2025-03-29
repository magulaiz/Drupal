<?php

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines the StatusCodeCacheContext service, for "per status code" caching.
 *
 * Cache context ID: 'status_code'.
 */
class RequestFormatCacheContext extends RequestStackCacheContextBase {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Status code');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $exception = $this->requestStack->getCurrentRequest()->attributes->get('exception');
    if ($exception) {
      return $exception->getStatusCode();
    }
    return '200';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
