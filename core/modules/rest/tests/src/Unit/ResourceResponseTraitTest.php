<?php

namespace Drupal\Tests\rest\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\rest\ResourceResponseInterface;
use Drupal\rest\ResourceResponseTrait;

/**
 * @coversDefaultClass \Drupal\rest\ResourceResponseTrait
 * @group rest
 */
class ResourceResponseTraitTest extends UnitTestCase {

  /**
   * @covers ::setResponseData()
   */
  public function testSetResponseData() {

    // Mock this trait using helper class.
    $trait_mock = $this->getMockForAbstractClass(HelperResourceResponseInterface::class);

    // Evaluate initial ResponseData value.
    $this->assertNotEquals($trait_mock->getResponseData(), "test_data");
    $this->assertEquals($trait_mock->getResponseData(), NULL);

    // Evaluate new value and type of ResponseData.
    $result = $trait_mock->setResponseData("test_data");
    $this->assertEquals("test_data", $trait_mock->getResponseData());
    $this->assertInstanceOf('Drupal\rest\ResourceResponseInterface', $result);

  }

}

/**
 * Helper class to test ResourceResponseTrait.
 *
 * @group rest
 */
abstract class HelperResourceResponseInterface implements ResourceResponseInterface {

  use ResourceResponseTrait;

}
