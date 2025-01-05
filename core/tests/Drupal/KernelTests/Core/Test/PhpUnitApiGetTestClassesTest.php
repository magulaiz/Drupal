<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Test;

use Drupal\Core\Test\PhpUnitTestDiscovery;
use Drupal\Core\Test\TestDiscovery;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\TextUI\Configuration\Builder;
use PHPUnit\TextUI\Configuration\TestSuiteBuilder;

/**
 * Tests ::getTestClasses() between TestDiscovery and PhpPUnitTestDiscovery.
 *
 * PhpPUnitTestDiscovery uses PHPUnit API to build the list of test classes,
 * while TestDiscovery uses Drupal legacy code.
 *
 * @group TestSuites
 * @group Test
 * @group #slow
 */
class PhpUnitApiGetTestClassesTest extends KernelTestBase {

  /**
   * Checks that Drupal legacy and PHPUnit API based discoveries are equal.
   */
  #[DataProvider('argumentsProvider')]
  #[IgnoreDeprecations]
  public function testEquality(array $suites, ?string $extension = NULL, ?string $directory = NULL): void {
    $testDiscovery = new TestDiscovery(
      $this->container->getParameter('app.root'),
      $this->container->get('class_loader')
    );
    $internalList = $testDiscovery->getTestClasses($extension, $suites, $directory);

    $phpUnitTestDiscovery = new PhpUnitTestDiscovery(
      $this->container->getParameter('app.root'),
      $this->container->get('class_loader')
    );
    $phpUnitList = $phpUnitTestDiscovery->getTestClasses($extension, $suites, $directory);

    // Downgrade results to make them comparable, working around bugs and
    // additions.
    // 1. Remove TestDiscovery empty groups.
    $internalList = array_filter($internalList);
    // 2. Remove 'file' keys from PHPUnit results.
    foreach ($phpUnitList as &$group) {
      foreach ($group as &$testClass) {
        unset($testClass['file']);
      }
    }
    // 3. Remove from PHPUnit results groups not found by TestDiscovery.
    $phpUnitList = array_intersect_key($phpUnitList, $internalList);
    // 4. Remove from PHPUnit groups classes not found by TestDiscovery.
    foreach ($phpUnitList as $groupName => &$group) {
      $group = array_intersect_key($group, $internalList[$groupName]);
    }
    // 5. Remove from PHPUnit test classes groups not found by TestDiscovery.
    foreach ($phpUnitList as $groupName => &$group) {
      foreach ($group as $testClassName => &$testClass) {
        $testClass['groups'] = array_intersect_key($testClass['groups'], $internalList[$groupName][$testClassName]['groups']);
      }
    }

    $this->assertEquals($internalList, $phpUnitList);
  }

  /**
   * Provides test data to ::testEquality.
   */
  public static function argumentsProvider(): \Generator {
    yield 'All tests' => ['suites' => []];
    yield 'Testsuite: functional-javascript' => ['suites' => ['PHPUnit-FunctionalJavascript']];
    yield 'Testsuite: functional' => ['suites' => ['PHPUnit-Functional']];
    yield 'Testsuite: kernel' => ['suites' => ['PHPUnit-Kernel']];
    yield 'Testsuite: unit' => ['suites' => ['PHPUnit-Unit']];
    yield 'Testsuite: build' => ['suites' => ['PHPUnit-Build']];
    yield 'Extension: system' => ['suites' => [], 'extension' => 'system'];
    yield 'Extension: system, testsuite: unit' => [
      'suites' => ['PHPUnit-Unit'],
      'extension' => 'system'
    ];
    yield 'Extension: system, directory' => [
      'suites' => [],
      'extension' => 'system',
      'directory' => 'core/modules/system/tests/src'
    ];
    yield 'Extension: system, testsuite: unit, directory' => [
      'suites' => ['PHPUnit-Unit'],
      'extension' => 'system',
      'directory' => 'core/modules/system/tests/src'
    ];
  }

}
