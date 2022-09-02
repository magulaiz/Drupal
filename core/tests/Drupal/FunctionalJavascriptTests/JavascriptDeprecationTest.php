<?php

namespace Drupal\FunctionalJavascriptTests;

/**
 * Tests JavaScript deprecation notices.
 *
 * @group javascript
 * @group legacy
 */
class JavascriptDeprecationTest extends WebDriverTestBase {

  protected static $modules = ['js_deprecation_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests JavaScript deprecation notices.
   */
  public function testJavaScriptDeprecation() {
    $this->expectDeprecation('JavaScript Deprecation: This function is deprecated for testing purposes.');
    $this->expectDeprecation('JavaScript Deprecation: This property is deprecated for testing purposes.');
    $this->drupalGet('js_deprecation_test');
    // Ensure that deprecation message from previous page loads will be
    // detected.
    $this->drupalGet('user');
  }

}
