<?php

namespace Drupal\Core\CacheDecorator;

@trigger_error('The ' . __NAMESPACE__ . '\CacheDecoratorInterface is deprecated in drupal:11.0-dev and is removed from drupal:11.0.0. Last/only usage was removed in 2020. See https://www.drupal.org/project/drupal/issues/3397488', E_USER_DEPRECATED);

/**
 * Defines an interface for cache decorator implementations.
 *
 * @deprecated in drupal:11.0-dev and is removed from drupal:11.0.0.
 *  Last/only usage was removed in 2020.
 *
 * @see https://www.drupal.org/project/drupal/issues/3397488
 */
interface CacheDecoratorInterface {

  /**
   * Specify the key to use when writing the cache.
   */
  public function setCacheKey($key);

  /**
   * Write the cache.
   */
  public function writeCache();

}
