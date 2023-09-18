<?php

namespace Drupal\Tests\Core\Test;

use Drupal\Tests\UnitTestCase;
use Drupal\Tests\Listeners\TimeLimitListenerTrait;

/**
 * @coversDefaultClass \Drupal\Tests\Listeners\TimeLimitListenerTrait
 * @group Test
 * @runTestsInSeparateProcesses
 */
class TimeLimitListenerTraitTest extends UnitTestCase {

  public function provideEnabled() {
    return [
      [TRUE, 'TRUE'],
      [TRUE, 'true'],
      [FALSE, 'false'],
      [FALSE, 'anything_other_than_true'],
      [FALSE, ''],
      [FALSE, 0],
      [FALSE, 1],
      [FALSE, NULL],
    ];
  }

  /**
   * @dataProvider provideEnabled
   * @covers ::timeLimitEnabled
   */
  public function testTimeLimitDisabled($expected, $value) {
    $timer = new TimeLimitTestClass();

    $ref_disabled = new \ReflectionMethod($timer, 'timeLimitEnabled');
    $ref_disabled->setAccessible(TRUE);

    // Set up our environmental variables.
    if ($value === NULL) {
      putenv('DRUPAL_TEST_ENABLE_TIME_LIMIT');
    }
    else {
      putenv("DRUPAL_TEST_ENABLE_TIME_LIMIT=$value");
    }
    $this->assertSame($expected, $ref_disabled->invoke($timer));
  }

}

class TimeLimitTestClass {

  use TimeLimitListenerTrait;

}
