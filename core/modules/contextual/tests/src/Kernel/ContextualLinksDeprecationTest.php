<?php

namespace Drupal\Tests\contextual\Kernel;

use Drupal\contextual\Plugin\views\field\ContextualLinks;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\contextual\Plugin\views\field\ContextualLinks
 * @group legacy
 */
class ContextualLinksDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['contextual'];

  /**
   * Tests deprecation of constructing a ContextualLinks object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testContextualLinksConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\contextual\Plugin\views\field\ContextualLinks::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new ContextualLinks(
      [],
      '',
      [],
    );
  }

}
