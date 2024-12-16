<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Block;

use Drupal\Core\Path\PathMatcherInterface;

/**
 * A class implementing PatchMatcherInterface for testing purposes.
 */
class StubPathMatcher implements PathMatcherInterface {

  /**
   * {@inheritdoc}
   */
  public function matchPath($path, $patterns) {
  }

  /**
   * {@inheritdoc}
   */
  public function isFrontPage() {
    return FALSE;
  }

}
