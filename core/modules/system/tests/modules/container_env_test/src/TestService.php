<?php

declare(strict_types=1);

namespace Drupal\container_env_test;

/**
 * Service class for testing container env.
 */
class TestService {

  const EXAMPLE = 1;

  /**
   * Constructs a new TestService instance.
   */
  public function __construct(public string $parameter) {
  }

}
