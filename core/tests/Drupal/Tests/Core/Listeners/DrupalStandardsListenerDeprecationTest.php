<?php

namespace Drupal\Tests\Core\Listeners;

use Drupal\Tests\UnitTestCase;

/**
 * Test deprecation error handling by DrupalStandardsListener.
 *
 * DrupalStandardsListener has a dependency on composer/composer, so we can't
 * test it directly. However, we can create a test which is annotated as
 * covering a deprecated class. This way we can know whether the standards
 * listener process ignores deprecation errors.
 *
 * Note that this test is not annotated as "@cover"-ing anything, because that
 * would trigger a deprecation error.
 *
 * @group Listeners
 */
class DrupalStandardsListenerDeprecationTest extends UnitTestCase {

  /**
   * Exercise DrupalStandardsListener's coverage validation.
   */
  public function testDeprecation() {
    // Meaningless assertion so this test is not risky.
    $this->assertTrue(TRUE);
  }

}
