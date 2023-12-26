<?php

namespace Drupal\Tests\Core\Extension\Requirement;

use Drupal\Core\Extension\Requirement\Requirement;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\Requirement
 *
 * @group Extension
 */
class RequirementTest extends UnitTestCase {

  /**
   * @covers ::create
   */
  public function testArrayAccess() {
    $requirement = Requirement::create()
      ->setTitle("Alice in Wonderland")
      ->setDescription("It's no use going back to yesterday, because I was a different person then.")
      ->setValue("small");

    // Test array access.
    $this->assertEquals("Alice in Wonderland", $requirement['title']);
    $this->assertEquals("It's no use going back to yesterday, because I was a different person then.", $requirement['description']);
    $this->assertEquals("small", $requirement['value']);
    $this->assertEquals(RequirementSeverity::OK, $requirement['severity']);
  }

}
