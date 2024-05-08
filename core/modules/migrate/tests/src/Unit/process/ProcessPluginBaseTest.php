<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit\process;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\ProcessPluginBase as CoreProcessPluginBase;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the base process plugin class.
 *
 * @group migrate
 */
#[CoversClass(\Drupal\migrate\ProcessPluginBase::class)]
class ProcessPluginBaseTest extends UnitTestCase {

  /**
   * Tests stopping the pipeline.
   */
  public function testStopPipeline() {
    $plugin = new ProcessPluginBase([], 'plugin_id', []);
    $this->assertFalse($plugin->isPipelineStopped());
    $stopPipeline = (new \ReflectionClass($plugin))->getMethod('stopPipeline');
    $stopPipeline->invoke($plugin);
    $this->assertTrue($plugin->isPipelineStopped());
    $plugin->reset();
    $this->assertFalse($plugin->isPipelineStopped());
  }

}

/**
 * Extends ProcessPluginBase as a non-abstract class.
 */
class ProcessPluginBase extends CoreProcessPluginBase {

}
