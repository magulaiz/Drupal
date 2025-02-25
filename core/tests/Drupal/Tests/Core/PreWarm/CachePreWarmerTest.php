<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\PreWarm;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\PreWarm\CachePreWarmer;
use Drupal\Core\PreWarm\PreWarmableInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \Drupal\Core\PreWarm\CachePreWarmer
 * @group PreWarm
 */
class CachePreWarmerTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject|ClassResolverInterface $classResolver;

  /**
   * @var array<\Drupal\Core\PreWarm\PreWarmableInterface|\PHPUnit\Framework\MockObject\MockObject>
   */
  protected array $preWarmableServices;

  /**
   * @var int
   */
  protected int $warmedCount = 0;

  public function testNoServices(): void {
    $classResolver = $this->createMock(ClassResolverInterface::class);
    $classResolver->expects($this->never())
      ->method('getInstanceFromDefinition');

    $prewarmer = new CachePreWarmer($classResolver, []);

    $this->assertFalse($prewarmer->preWarmOneCache());
    $this->assertFalse($prewarmer->preWarmAllCaches());
  }

  protected function setupCacheServices(): void {
    $this->classResolver = $this->createMock(ClassResolverInterface::class);

    $services = [
      'service1',
      'service2',
      'service3',
      'service4',
    ];
    $returnMap = [];
    foreach ($services as $serviceId) {
      $this->preWarmableServices[$serviceId] = $this->createMock(PrewarmableInterface::class);
      $this->preWarmableServices[$serviceId]->method('preWarm')
        ->willReturnCallback(function () use ($serviceId) {
          $this->preWarmableServices[$serviceId]->warmed = TRUE;
          $this->warmedCount++;
        });

      $returnMap[] = [$serviceId, $this->preWarmableServices[$serviceId]];
    }

    $this->classResolver->method('getInstanceFromDefinition')
      ->willReturnMap($returnMap);
  }

  /**
   * @covers ::preWarmOneCache
   */
  public function testPreWarmOnlyOne(): void {
    $this->setupCacheServices();

    $preWarmer = new CachePreWarmer($this->classResolver, array_keys($this->preWarmableServices));

    $preWarmer->preWarmOneCache();

    $this->assertEquals(1, $this->warmedCount);
  }

  /**
   * @covers ::preWarmOneCache
   */
  public function testPreWarmByOne(): void {
    $this->setupCacheServices();

    $preWarmer = new CachePreWarmer($this->classResolver, array_keys($this->preWarmableServices));

    while ($preWarmer->preWarmOneCache()) {

    }

    $this->assertEquals(4, $this->warmedCount);
    foreach ($this->preWarmableServices as $service) {
      $this->assertTrue($service->warmed);
    }
  }

  /**
   * @covers ::preWarmAllCaches
   */
  public function testPreWarmAll(): void {
    $this->setupCacheServices();

    $preWarmer = new CachePreWarmer($this->classResolver, array_keys($this->preWarmableServices));

    $preWarmer->preWarmAllCaches();

    $this->assertEquals(4, $this->warmedCount);
    foreach ($this->preWarmableServices as $service) {
      $this->assertTrue($service->warmed);
    }

    $this->assertFalse($preWarmer->preWarmAllCaches());
  }

}
