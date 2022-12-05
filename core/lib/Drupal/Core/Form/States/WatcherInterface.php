<?php

namespace Drupal\Core\Form\States;

/**
 * Interface WatcherStateInterface.
 *
 * @package Drupal\Core\Form.
 */
interface WatcherInterface extends BaseStateInterface, WatchableInterface {

  /**
   * Name of empty state of element.
   */
  public const EMPTY = 'empty';

  /**
   * Name of filled state of element.
   */
  public const FILLED = 'filled';

  /**
   * Name of checked state of element.
   */
  public const CHECKED = 'checked';

  /**
   * Name of unchecked state of element.
   */
  public const UNCHECKED = 'unchecked';

  /**
   * Name of expanded state of element.
   */
  public const EXPANDED = 'expanded';

  /**
   * Name of collapsed state of element.
   */
  public const COLLAPSED = 'collapsed';

  /**
   * Name of key used for value comparison.
   */
  public const VALUE = 'value';

  /**
   * Selector string getter.
   *
   * @return string
   *   Selector value.
   */
  public function getSelector(): string;

  /**
   * Remote condition for checked element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isChecked(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for collapsed element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isCollapsed(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for expanded element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isExpanded(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for invalid element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isInvalid(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for collapsed element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isIrrelevant(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for readonly element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isReadonly(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for readwrite element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isReadwrite(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for relevant element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isRelevant(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for touched element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isTouched(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for unchecked element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isUnchecked(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for untouched element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isUntouched(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for valid element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isValid(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for empty element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isEmpty(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for filled element.
   *
   * @param bool $condition
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function isFilled(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   * Remote condition for element with provided value.
   *
   * @param mixed $value
   *   Condition of selected state.
   * @param bool $negate
   *   Flag to negate the condition. By default, condition set as it is.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function valueEqualTo(mixed $value, bool $negate = FALSE): static;

  /**
   * Remote condition for element at custom state.
   *
   * @param string $state
   *   Name of the custom state.
   * @param mixed $condition
   *   Condition of selected state.
   *
   * @return \Drupal\Core\Form\States\WatcherInterface
   *   Watcher instance.
   */
  public function setCustomCondition(string $state, mixed $condition): static;

}
