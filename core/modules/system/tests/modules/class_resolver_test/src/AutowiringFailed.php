<?php

declare(strict_types=1);

namespace Drupal\class_resolver_test;

use Drupal\Core\Lock\LockBackendInterface;

/**
 * Provides a class for testing autowiring failed.
 */
final class AutowiringFailed {

  /**
   * Constructs a new ContainerInjection object.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   */
  public function __construct(
    public readonly LockBackendInterface $lock,
  ) {}

}
