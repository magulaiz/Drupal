<?php

namespace Drupal\FunctionalTests;

use Drupal\Tests\BrowserTestBase;

/**
 * Test to ensure that functional tests produce debug HTML output when required.
 *
 * @see \Drupal\Tests\Core\Test\PhpUnitCliTest::testFunctionalTestDebugHtmlOutput
 *
 * @group TestSuites
 */
class FunctionalTestDebugHtmlOutputTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Creates one page of debug HTML output.
   */
  public function testFunctionalTestDebugHtmlOutput(): void {
    $this->drupalGet('/');
  }

}
