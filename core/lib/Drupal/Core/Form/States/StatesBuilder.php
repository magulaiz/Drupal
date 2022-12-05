<?php

namespace Drupal\Core\Form\States;

class StatesBuilder implements StatesBuilderInterface {

  /**
   * States builder constructor.
   *
   * @param \Drupal\Core\State\StateInterface[] $states
   *   States storage.
   */
  public function __construct(
    protected array $states = []
  ) {}

  /**
   * {@inheritdoc}
   */
  public function watch(string $selector, array $conditions = []): WatcherInterface {
    return new Watcher($selector, $conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function state(string $state = '', WatchableInterface ...$watchers): StateInterface {
    return new State($state, $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function and(WatchableInterface ...$watchers): WatcherConditionGroupInterface {
    return new WatcherConditionGroup('and', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function or(WatchableInterface ...$watchers): WatcherConditionGroupInterface {
    return new WatcherConditionGroup('or', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function xor(WatchableInterface ...$watchers): WatcherConditionGroupInterface {
    return new WatcherConditionGroup('xor', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function addStates(StateInterface ...$states): static {
    foreach ($states as $state) {
      $this->states[] = $state;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $result = [];
    /** @var \Drupal\Core\Form\States\StateInterface $state */
    foreach ($this->states as $state) {
      $state_name = $state->getState();
      $result[$state_name] = ($result[$state_name] ?? []) + $state->toArray();
    }
    return $result;
  }

}
