<?php

declare(strict_types=1);

namespace Drupal\Component\Datetime;

use Psr\Clock\ClockInterface;

/**
 * A clock that returns the current system time.
 */
class Clock implements ClockInterface {

  public function __construct(
    private readonly ?\DateTimeZone $timezone = NULL,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function now(): \DateTimeImmutable {
    return new \DateTimeImmutable('now', $this->timezone);
  }

}
