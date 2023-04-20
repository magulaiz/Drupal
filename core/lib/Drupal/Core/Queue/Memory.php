<?php

namespace Drupal\Core\Queue;

use Drupal\Component\Datetime\TimeInterface;

/**
 * Static queue implementation.
 *
 * This allows "undelayed" variants of processes relying on the Queue
 * interface. The queue data resides in memory. It should only be used for
 * items that will be queued and dequeued within a given page request.
 *
 * @ingroup queue
 */
class Memory implements QueueInterface {

  /**
   * The queue data.
   *
   * @var array
   */
  protected $queue;

  /**
   * Counter for item ids.
   *
   * @var int
   */
  protected $idSequence;

  /**
   * The time service.
   */
  protected readonly TimeInterface $time;

  /**
   * Constructs a Memory object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct($time = NULL) {
    $this->queue = [];
    $this->idSequence = 0;
    if (is_string($time)) {
      @trigger_error('Calling ' . __METHOD__ . '() with the $name argument instead of $time argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3161659', E_USER_DEPRECATED);
      $time = \Drupal::time();
    }
    elseif (!$time) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $time argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3161659', E_USER_DEPRECATED);
      $time = \Drupal::time();
    }
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function createItem($data) {
    $item = new \stdClass();
    $item->item_id = $this->idSequence++;
    $item->data = $data;
    $item->created = $this->time->getCurrentTime();
    $item->expire = 0;
    $this->queue[$item->item_id] = $item;
    return $item->item_id;
  }

  /**
   * {@inheritdoc}
   */
  public function numberOfItems() {
    return count($this->queue);
  }

  /**
   * {@inheritdoc}
   */
  public function claimItem($lease_time = 30) {
    foreach ($this->queue as $key => $item) {
      if ($item->expire == 0) {
        $item->expire = $this->time->getCurrentTime() + $lease_time;
        $this->queue[$key] = $item;
        return $item;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($item) {
    unset($this->queue[$item->item_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function releaseItem($item) {
    if (isset($this->queue[$item->item_id]) && $this->queue[$item->item_id]->expire != 0) {
      $this->queue[$item->item_id]->expire = 0;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createQueue() {
    // Nothing needed here.
  }

  /**
   * {@inheritdoc}
   */
  public function deleteQueue() {
    $this->queue = [];
    $this->idSequence = 0;
  }

}
