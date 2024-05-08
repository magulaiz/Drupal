<?php

declare(strict_types=1);

namespace Drupal\Tests\serialization\Unit\Encoder;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\serialization\Encoder\JsonEncoder;
use Drupal\Tests\UnitTestCase;

/**
 * @group serialization
 */
#[CoversClass(\Drupal\serialization\Encoder\JsonEncoder::class)]
class JsonEncoderTest extends UnitTestCase {

  /**
   * Tests the supportsEncoding() method.
   */
  public function testSupportsEncoding() {
    $encoder = new JsonEncoder();

    $this->assertTrue($encoder->supportsEncoding('json'));
    $this->assertTrue($encoder->supportsEncoding('ajax'));
    $this->assertFalse($encoder->supportsEncoding('xml'));
  }

}
