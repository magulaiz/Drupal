<?php

namespace Drupal\Core\Path;

/**
 * Provides an interface for URL path matchers.
 */
interface PathMatcherInterface {

  /**
   * Checks if a path matches any pattern in a set of patterns.
   *
   * @param string $path
   *   The path to match.
   * @param string|string[] $patterns
   *   A set of patterns separated by a newline.
   *
   * @return bool
   *   TRUE if the path matches a pattern, FALSE otherwise.
   */
  public function matchPath(string $path, string|array $patterns): bool;

  /**
   * Checks if the current page is the front page.
   *
   * @return bool
   *   TRUE if the current page is the front page.
   */
  public function isFrontPage();

}
