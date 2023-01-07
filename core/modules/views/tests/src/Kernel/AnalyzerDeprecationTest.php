<?php

namespace Drupal\Tests\views\Kernel;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Analyzer;

/**
 * @coversDefaultClass \Drupal\views\Analyzer
 * @group legacy
 */
class AnalyzerDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views'];

  /**
   * Tests deprecation of constructing an Analyzer object without the renderer argument.
   *
   * @covers ::__construct
   */
  public function testAnalyzerConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\views\Analyzer::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new Analyzer(
      $this->prophesize(ModuleHandlerInterface::class)->reveal()
    );
  }

}
