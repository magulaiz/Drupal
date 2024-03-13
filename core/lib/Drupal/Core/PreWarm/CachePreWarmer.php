<?php

namespace Drupal\Core\PreWarm;

use Drupal\Core\DependencyInjection\ClassResolverInterface;

// cspell:ignore ABCDEF FCDABE BEDAFC

/**
 * Prewarms caches for services that implement PreWarmableInterface.
 *
 * Takes a list of prewarmable services and prewarms them at random.
 * Randomization is used because whenever two or more requests are building
 * caches, the most benefit is gained by minimizing duplication. For example
 * two requests rely on the same six services but these services are requested
 * at different times, one request builds caches for the other and vice versa.
 *
 * No randomization:
 *
 * ABCDEF
 * ABCDEF
 *
 * Randomization:
 *
 * ABCDEF
 * FCDABE
 *
 * Randomization and three requests:
 *
 * ABCDEF
 * FCDABE
 * BEDAFC
 *
 * @see Drupal\Core\PreWarm\PreWarmableInterface
 * @see Drupal\Core\DrupalKernel::handle()
 * @see Drupal\Core\LockBackendAbstract::wait()
 * @see Drupal\Core\Routing\RouteProvider::preLoadRoutes()
 */
class CachePreWarmer implements CachePreWarmerInterface {

  /**
   * Whether to prewarm caches at the end of the request.
   */
  protected bool $needsPreWarming = FALSE;

  /**
   * Called services.
   *
   * A list of services we have already prewarmed, so they can be skipped on
   * subsequent calls.
   *
   * @var string[]
   */
  protected array $calledServices = [];

  public function __construct(protected readonly ClassResolverInterface $classResolver, protected readonly array $serviceIds) {}

  /**
   * {@inheritdoc}
   */
  public function preWarmOneCache(): void {
    $candidates = array_diff($this->serviceIds, $this->calledServices);
    // If we've tried to prewarm all the available services, don't try to do it
    // again. We're most likely to hit this case if a request comes in late
    // during a stampede and everything was warmed up just before we reached
    // here.
    if ($candidates) {
      // Pick a prewarmable service to prewarm the cache for at random.
      $key = array_rand($candidates);
      $this->calledServices[] = $key;
      $service = $this->classResolver->getInstanceFromDefinition($this->serviceIds[$key]);
      $service->preWarm();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preWarmAllCaches(): void {
    $candidates = $this->serviceIds;
    shuffle($candidates);
    while ($candidates) {
      $key = key($candidates);
      $service = $this->classResolver->getInstanceFromDefinition($candidates[$key]);
      unset($candidates[$key]);
      $service->preWarm();
    }
  }

}
