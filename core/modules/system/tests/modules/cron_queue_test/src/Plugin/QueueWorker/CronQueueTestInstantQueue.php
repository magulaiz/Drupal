<?php

declare(strict_types=1);

namespace Drupal\cron_queue_test\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;

/**
 * Hello.
 *
 * @QueueWorker(
 *   id = "instant_queue",
 *   title = @Translation("Custom Queue"),
 *   cron = {"time" = 60}
 * )
 */
class CronQueueTestInstantQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {


  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, QueueInterface $queue) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    \Drupal::logger('instant_queue')->info(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(DependencyInjectionContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('queue')->get('instant_queue', TRUE)
    );
  }

}
