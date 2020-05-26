<?php


namespace Drupal\Tests\system\Kernel;


use Drupal\KernelTests\KernelTestBase;

class DecoratedServiceTest extends KernelTestBase {

  protected static $modules = [
    'decorated_service_test',
  ];

  public function testDecoratedServiceId() {
    $this->assertEquals('test_service', \Drupal::service('test_service')->_serviceId);
  }

}
