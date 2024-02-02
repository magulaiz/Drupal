<?php

namespace Drupal\Tests\system\Kernel;

<<<<<<< HEAD
=======
use Drupal\Component\DependencyInjection\ReverseContainer;
>>>>>>> upstream/11.x
use Drupal\decorated_service_test\TestServiceDecorator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test handling of decorated services in DependencySerializationTraitPass.
 *
 * @group system
 */
class DecoratedServiceTest extends KernelTestBase {

  protected static $modules = [
    'decorated_service_test',
  ];

  /**
   * Check that decorated services keep their original service ID.
   */
  public function testDecoratedServiceId() {
    // Service decorated once.
    $test_service = $this->container->get('test_service');
<<<<<<< HEAD
    $this->assertEquals('test_service', $test_service->_serviceId);
=======
    $this->assertEquals('test_service', $this->container->get(ReverseContainer::class)->getId($test_service));
>>>>>>> upstream/11.x
    $this->assertInstanceOf(TestServiceDecorator::class, $test_service);

    // Service decorated twice.
    $test_service2 = $this->container->get('test_service2');
<<<<<<< HEAD
    $this->assertEquals('test_service2', $test_service2->_serviceId);
=======
    $this->assertEquals('test_service2', $this->container->get(ReverseContainer::class)->getId($test_service2));
>>>>>>> upstream/11.x
    $this->assertInstanceOf(TestServiceDecorator::class, $test_service2);
  }

}
