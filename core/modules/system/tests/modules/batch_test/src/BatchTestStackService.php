<?php

namespace Drupal\batch_test;

use Drupal\Core\State\StateInterface;

/**
 * Stores or retrieves traced execution data.
 */
class BatchTestStackService {

  /**
   * The key used for state.
   */
  const STATE_KEY = 'batch_test.stack';

  /**
   * Creates a new BatchTestStackService instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   */
  public function __construct(
    protected StateInterface $state
  ) {}

  /**
   * Helper function: Stores or retrieves traced execution data.
   */
  public function batchTestStack($data = NULL, $reset = FALSE) {
    if ($reset) {
      $this->state->delete(self::STATE_KEY);
    }
    if (!isset($data)) {
      return $this->state->get(self::STATE_KEY);
    }
    $stack = $this->state->get(self::STATE_KEY);
    $stack[] = $data;
    $this->state->set(self::STATE_KEY, $stack);
  }

}
