<?php

namespace Drupal\Core;

/**
 * Allows to delegate cron tasks to services.
 *
 * Use the tag cron on the service that implements this interface.
 *
 * @see hook_cron()
 */
interface CronSubscriberInterface {

  /**
   * Executes a cron task.
   */
  public function runCron(): void;

}
