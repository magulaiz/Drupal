<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Block;

use Drupal\Core\Path\PathMatcherInterface;

/**
 * A class implementing PatchMatcherInterface for testing purposes.
 */
class StubPathMatcher implements PathMatcherInterface {

  /**
   * {@inheritDoc}
   */
  public function matchPath($path, $patterns) {
  }

  /**
   * {@inheritDoc}
   */
  public function isFrontPage() {
    return FALSE;
  }

}

