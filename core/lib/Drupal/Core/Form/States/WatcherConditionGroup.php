<?php

namespace Drupal\Core\Form\States;

/**
 * Watcher condition group.
 */
class WatcherConditionGroup implements WatcherConditionGroupInterface {

  /**
   * Watcher condition group.
   *
   * @param string $operator
   *   Condition for the group.
   * @param \Drupal\Core\Form\States\WatchableInterface[] $watchers
   *   Watcher storage.
   */
  public function __construct(
    protected readonly string $operator = 'and',
    protected array $watchers = [],
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getConditionOperator(): string {
    return $this->operator;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $array = [];
    foreach ($this->watchers as $key => $watcher) {
      if ($watcher instanceof WatcherConditionGroupInterface) {
        if ($key !== 0) {
          $array[] = $watcher->getConditionOperator();
        }
        $array[] = $watcher->toArray($key === 0);
      }
      if ($watcher instanceof WatcherInterface) {
        $selector = $watcher->getSelector();
        $array[$selector] = ($array[$selector] ?? []) + $watcher->toArray();
      }
    }
    return $array;
  }

}
