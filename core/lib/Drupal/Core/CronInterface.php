<?php

namespace Drupal\Core;

/**
 * An interface for running cron tasks.
 *
 * @see https://www.drupal.org/cron
 */
interface CronInterface {

  /**
   * The default time duration in seconds spent calling a queue worker.
   *
   * @var int
   */
  public const DEFAULT_QUEUE_CRON_TIME = 15;

  /**
   * Executes a cron run.
   *
   * Do not call this function from a test. Use $this->cronRun() instead.
   *
   * @return bool
   *   TRUE upon success, FALSE otherwise.
   */
  public function run();

}
