<?php

namespace Drupal\Tests\mongodb\Kernel\mongodb;

use Drupal\KernelTests\Core\Database\TemporaryQueryTestBase;

/**
 * Tests the temporary query functionality.
 *
 * @group Database
 */
class TemporaryQueryTest extends TemporaryQueryTestBase {

  /**
   * Confirms that temporary tables work.
   */
  public function testTemporaryQuery() {
    $this->markTestSkipped('The MongoDB database driver does not support temporary tables.');
  }

}
