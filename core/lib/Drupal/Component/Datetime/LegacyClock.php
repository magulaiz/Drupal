<?php

declare(strict_types=1);

namespace Drupal\Component\Datetime;

use Psr\Clock\ClockInterface;

/**
 * A legacy clock that wraps TimeInterface to return the current system time.
 *
 * @internal
 */
class LegacyClock implements ClockInterface {

  public function __construct(
    private readonly TimeInterface $time,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function now(): \DateTimeImmutable {
    return new \DateTimeImmutable('@' . $this->time->getCurrentTime());
  }

}
