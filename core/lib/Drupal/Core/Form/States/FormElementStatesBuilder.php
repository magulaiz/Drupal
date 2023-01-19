<?php

namespace Drupal\Core\Form\States;

class FormElementStatesBuilder implements FormElementStatesBuilderInterface {

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
  public function watch(string $selector, array $conditions = []): FormElementWatcherInterface {
    return new FormElementWatcher($selector, $conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function state(string $state = '', FormElementWatchableInterface ...$watchers): FormElementStateInterface {
    return new FormElementState($state, $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function and(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface {
    return new FormElementWatcherConditionGroup('and', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function or(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface {
    return new FormElementWatcherConditionGroup('or', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function xor(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface {
    return new FormElementWatcherConditionGroup('xor', $watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function addStates(FormElementStateInterface ...$states): static {
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
    /** @var \Drupal\Core\Form\States\FormElementStateInterface $state */
    foreach ($this->states as $state) {
      $state_name = $state->getState();
      $result[$state_name] = ($result[$state_name] ?? []) + $state->toArray();
    }
    return $result;
  }

}
