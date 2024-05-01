<?php

namespace Drupal\TestTools;

/**
 * @todo
 *
 * @internal
 */
class TestRunResult {

  /**
   * @todo
   */
  public function __construct(
    public readonly int $status,
    public readonly string $output,
    public readonly string $errorOutput,
    public readonly string $xmlLog,
  ) {
  }

}
