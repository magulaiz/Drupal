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
  public function testEquality(array $suites): void {
    $testDiscovery = new TestDiscovery(
      $this->container->getParameter('app.root'),
      $this->container->get('class_loader')
    );
    $internalList = $testDiscovery->getTestClasses(NULL, $suites);

    $phpUnitTestDiscovery = new PhpUnitTestDiscovery(
      $this->container->getParameter('app.root'),
      $this->container->get('class_loader')
    );
    $phpUnitList = $phpUnitTestDiscovery->getTestClasses(NULL, $suites);

    $this->assertEquals(array_filter($internalList), $phpUnitList);
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
  }

}
