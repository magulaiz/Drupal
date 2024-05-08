<?php

declare(strict_types=1);

namespace Drupal\Tests\serialization\Unit\Normalizer;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\serialization\Normalizer\NullNormalizer;
use Drupal\Tests\UnitTestCase;

/**
 * @group serialization
 */
#[CoversClass(\Drupal\serialization\Normalizer\NullNormalizer::class)]
class NullNormalizerTest extends UnitTestCase {

  /**
   * The NullNormalizer instance.
   *
   * @var \Drupal\serialization\Normalizer\NullNormalizer
   */
  protected $normalizer;

  /**
   * The interface to use in testing.
   *
   * @var string
   */
  protected $interface = 'Drupal\Core\TypedData\TypedDataInterface';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->normalizer = new NullNormalizer($this->interface);
  }

  public function testSupportsNormalization() {
    $mock = $this->createMock('Drupal\Core\TypedData\TypedDataInterface');
    $this->assertTrue($this->normalizer->supportsNormalization($mock));
    // Also test that an object not implementing TypedDataInterface fails.
    $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
  }

  public function testNormalize() {
    $mock = $this->createMock('Drupal\Core\TypedData\TypedDataInterface');
    $this->assertNull($this->normalizer->normalize($mock));
  }

}
