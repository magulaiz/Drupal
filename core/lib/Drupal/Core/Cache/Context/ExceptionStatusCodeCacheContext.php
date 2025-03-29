<?php

namespace Drupal\Core\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Defines the ExceptionStatusCodeCacheContext service.
 *
 * Cache context ID: 'exception_status_code'.
 */
class ExceptionStatusCodeCacheContext extends RequestStackCacheContextBase {

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
    // If there's no exception status code, usually a 200, return '0' because we
    // don't know what might be set by response subscribers.
    return '0';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return new CacheableMetadata();
  }

}
