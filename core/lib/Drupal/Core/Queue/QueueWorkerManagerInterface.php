<?php

namespace Drupal\Core\Queue;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides an interface for a queue worker manager.
 *
 * @template-extends \Drupal\Component\Plugin\PluginManagerInterface<\Drupal\Core\Queue\QueueWorkerInterface>
 */
interface QueueWorkerManagerInterface extends PluginManagerInterface {

  /**
   * The default time duration in seconds spent calling a queue worker.
   *
   * @var int
   */
  public const DEFAULT_QUEUE_CRON_TIME = 15;

}
