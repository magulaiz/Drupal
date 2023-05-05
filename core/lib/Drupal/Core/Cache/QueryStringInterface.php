<?php

declare(strict_types=1);

namespace Drupal\Core\Cache;

/**
 * Defines an interface for the cache query string service.
 */
interface QueryStringInterface {

  /**
   * Resets the cache query string added to all CSS and JavaScript URLs.
   *
   * Changing the cache query string appended to CSS and JavaScript URLs forces
   * all browsers to fetch fresh files.
   */
  public function reset(): void;

  /**
   * Get the query string value.
   */
  public function get(): string;

}
