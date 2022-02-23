<?php

namespace Drupal\Tests\Component\Assertion;

use Drupal\Component\Assertion\Handle;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Test assertion handler component.
 *
 * @group Utility
 * @group legacy
 *
 * @coversDefaultClass \Drupal\Component\Utility\Mail
 */
class HandleTest extends TestCase {

  use ExpectDeprecationTrait;

  /**
   * Tests deprecation of the Handle class.
   *
   * @group legacy
   */
  public function testFormatDisplayName() {
    $this->expectDeprecation('Drupal\Component\Assertion\Handle is deprecated in drupal:9.3.0 and is removed from drupal:10.0.0. Instead, use assert_options(ASSERT_EXCEPTION, TRUE). See https://drupal.org/node/3105918');
    $this->getMockBuilder(Handle::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

}
