<?php

namespace Drupal\Core\Form\States;

/**
 * States builder interface.
 */
interface StatesBuilderInterface {

  /**
   * State instance getter.
   *
   * Help to determine possible state of element.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return \Drupal\Core\Form\States\StateInterface
   *   State instance.
   */
  public function state(string $state = '', WatcherInterface ...$watchers): StateInterface;

  /**
   * Watcher instance getter.
   *
   * Creates the watcher instance which can used to define remote conditions.
   *
   * @param string $selector
   *   Selector string for element used for remote condition.
   * @param array $conditions
   *   Expected remote conditions for watched element.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function watch(string $selector, array $conditions = []): WatcherInterface;

  /**
   * Add states to the state storage.
   *
   * @param \Drupal\Core\Form\States\StateInterface ...$states
   *   States defined to the element.
   *
   * @return $this
   *   States builder instance.
   */
  public function addStates(StateInterface ...$states): static;

  /**
   * Add AND condition group.
   *
   * @param \Drupal\Core\Form\States\WatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function and(WatchableInterface ...$watchers): static;

  /**
   * Add OR condition group.
   *
   * @param \Drupal\Core\Form\States\WatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function or(WatchableInterface ...$watchers): static;

  /**
   * Add XOR condition group.
   *
   * @param \Drupal\Core\Form\States\WatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function xor(WatchableInterface ...$watchers): static;

  /**
   * Convert states to the array.
   *
   * @return \Drupal\Core\Form\States\StateInterface[]
   *   Array that prepared for #states of form element.
   */
  public function toArray(): array;

}
