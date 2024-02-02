<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Http;

use Drupal\Core\Http\RequestStack;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Http\RequestStack
 * @group legacy
 *
 * @todo Remove this in Drupal 11 https://www.drupal.org/node/3265121
 */
class RequestStackLegacyTest extends UnitTestCase {

  /**
   * Tests deprecation message in our subclassed RequestStack.
   *
   * @covers ::getMainRequest
   */
  public function testGetMainRequestDeprecation() {
    $stack = new RequestStack();

<<<<<<< HEAD
    $this->expectDeprecation('Drupal\Core\Http\RequestStack::getMasterRequest() is deprecated in drupal:9.3.0 and is removed from drupal:10.0.0. Use getMainRequest() instead. See https://www.drupal.org/node/3253744');
    $this->assertNull($stack->getMasterRequest());
=======
    $this->expectDeprecation('The Drupal\Core\Http\RequestStack is deprecated in drupal:10.0.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3265357');
    $this->assertNull($stack->getMainRequest());
>>>>>>> upstream/11.x
  }

}
