<?php

declare(strict_types=1);

namespace Drupal\cron_queue_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Creates queue items for testing instant queues.
 */
class CronQueueTestInstantQueueController extends ControllerBase {

  /**
   * Create instant_queue queue items.
   *
   * @param int $count
   *   Number of queue items to create.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON object with status.
   */
  public function createItems(int $count): JsonResponse {
    $queue = \Drupal::queue('instant_queue', TRUE);
    $queue->createQueue();
    for ($cnt = 1; $cnt <= $count; $cnt++) {
      $queue->createItem(['instant_queue' => $cnt]);
    }
    return new JsonResponse(['status' => TRUE]);
  }

}
