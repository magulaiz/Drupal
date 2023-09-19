<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Tests\UnitTestCase;

/**
 * Tests that the Generic module test exists for all modules.
 *
 * @group Extension
 */
class GenericTestExistsTest extends UnitTestCase {

  /**
   * Lists module that do not require a Generic test.
   */
  protected $modulesNoTest = ['help_topics'];

  /**
   * Tests that the Generic module test exists for all modules.   */
  public function testGenericTestExists() {
    $base_directory = $this->root . '/core/modules';
    chdir($base_directory);
    $modules = array_diff(scandir($base_directory), ['.', '..']);
    $all_tests = glob("*/tests/src/Functional/GenericTest.php");
    $actual = array_map(function ($dir) {
      return explode('/', $dir, 2)[0];
    }, $all_tests);
    $actual = array_merge($actual, $this->modulesNoTest);
    sort($actual);
    $missing_tests = array_diff($modules, $actual);
    // Use assertSame so the error output includes the diff array.
    $this->assertSame([], $missing_tests);
  }

}
