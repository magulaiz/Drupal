<?php

namespace Drupal\Core\Form\States;

/**
 * States builder interface.
 */
interface FormElementStatesBuilderInterface {

  /**
   * State instance getter.
   *
   * Help to determine possible state of element.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return \Drupal\Core\Form\States\FormElementStateInterface
   *   State instance.
   */
  public function state(string $state = '', FormElementWatcherInterface ...$watchers): FormElementStateInterface;

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
   * @return \Drupal\Core\Form\States\FormElementWatcherInterface
   *   Watcher instance.
   */
  public function watch(string $selector, array $conditions = []): FormElementWatcherInterface;

  /**
   * Add states to the state storage.
   *
   * @param \Drupal\Core\Form\States\FormElementStateInterface ...$states
   *   States defined to the element.
   *
   * @return $this
   *   States builder instance.
   */
  public function addStates(FormElementStateInterface ...$states): static;

  /**
   * Add AND condition group.
   *
   * @param \Drupal\Core\Form\States\FormElementWatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function and(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface;

  /**
   * Add OR condition group.
   *
   * @param \Drupal\Core\Form\States\FormElementWatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function or(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface;

  /**
   * Add XOR condition group.
   *
   * @param \Drupal\Core\Form\States\FormElementWatchableInterface ...$watchers
   *   Watcher instances for AND condition group.
   *
   * @return $this
   *   State builder instance.
   */
  public function xor(FormElementWatchableInterface ...$watchers): FormElementWatcherConditionGroupInterface;

  /**
   * Convert states to the array.
   *
   * @return \Drupal\Core\Form\States\FormElementStateInterface[]
   *   Array that prepared for #states of form element.
   */
  public function toArray(): array;

}
