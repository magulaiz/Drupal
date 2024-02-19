<?php

declare(strict_types=1);

namespace Drupal\module_discovery_collision_test;

/**
 * Class for testing existing class collision.
 */
final class NewDiscoveryService {

  public function __construct(
    public readonly string $definitionSource,
  ) {
  }

}
