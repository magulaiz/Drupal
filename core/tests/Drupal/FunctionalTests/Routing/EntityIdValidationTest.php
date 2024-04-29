<?php

namespace Drupal\FunctionalTests\Routing;

use Drupal\Tests\BrowserTestBase;

/**
 * Show whether a large node ID can trigger an error 500.
 *
 * @group routing
 */
class EntityIdValidationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'taxonomy',
    'user',
  ];

  /**
   * @dataProvider largeEntityIdPaths()
   */
  public function testLargeEntityIds($value) {
    $this->drupalGet($value);
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Set of paths.
   *
   * @return array[]
   */
  public function largeEntityIdPaths() {
    return [
      ["/node/9223372036854776833"],
      ["/node/9223372036854776832"],
      ["/node/2147483648"],
      ["/node/2147483647"],
      ["/taxonomy/term/99999999999999999"],
      ["/user/99999999999999999"],
    ];
  }

}
