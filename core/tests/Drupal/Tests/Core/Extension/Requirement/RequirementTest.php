<?php

namespace Drupal\Tests\Core\Extension\Requirement;

use Drupal\Core\Extension\Requirement\BaseRequirement;
use Drupal\Core\Extension\Requirement\RequirementOk;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Requirement\BaseRequirement
 *
 * @group Extension
 */
class RequirementTest extends UnitTestCase {

  /**
   * @covers ::create
   */
  public function testCreate() {
    $requirement = RequirementOk::create()
      ->setTitle("Alice in Wonderland")
      ->setDescription("It's no use going back to yesterday, because I was a different person then.")
      ->setValue("small");

    // Test array access.
    $this->assertEquals("Alice in Wonderland", $requirement['title']);
    $this->assertEquals("It's no use going back to yesterday, because I was a different person then.", $requirement['description']);
    $this->assertEquals("small", $requirement['value']);
    $this->assertEquals(BaseRequirement::SEVERITY_OK, $requirement['severity']);
  }

}
