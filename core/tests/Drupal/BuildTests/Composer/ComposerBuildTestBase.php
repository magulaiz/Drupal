<?php

namespace Drupal\BuildTests\Composer;

use Drupal\BuildTests\Framework\BuildTestBase;

/**
 * Base class for Composer build tests.
 *
 * @coversNothing
 */
abstract class ComposerBuildTestBase extends BuildTestBase {

  /**
   * Assert that the VERSION constant in Drupal.php is the expected value.
   *
   * @param string $expectedVersion
   *   The expected version.
   * @param string $dir
   *   The path to the site root.
   *
   * @internal
   */
  protected function assertDrupalVersion(string $expectedVersion, string $dir): void {
    $drupal_php_path = $dir . '/core/lib/Drupal.php';
    $this->assertFileExists($drupal_php_path);

    // Read back the Drupal version that was set and assert it matches expectations.
    $this->executeCommand("php -r 'include \"$drupal_php_path\"; print \Drupal::VERSION;'");
    $this->assertCommandSuccessful();
    $this->assertCommandOutputContains($expectedVersion);
  }

}
