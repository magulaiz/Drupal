<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface StateInterface extends BaseStateInterface {

  /**
   * Name of enabled state of element.
   */
  public const ENABLED = 'enabled';

  /**
   * Name of disabled state of element.
   */
  public const DISABLED = 'disabled';

  /**
   * Name of required state of element.
   */
  public const REQUIRED = 'required';

  /**
   * Name of optional state of element.
   */
  public const OPTIONAL = 'optional';

  /**
   * Name of visible state of element.
   */
  public const VISIBLE = 'visible';

  /**
   * Name of invisible state of element.
   */
  public const INVISIBLE = 'invisible';

  /**
   * Name of checked state of checkbox/radio element.
   */
  public const CHECKED = 'checked';

  /**
   * Name of unchecked state of checkbox/radio element.
   */
  public const UNCHECKED = 'unchecked';

  /**
   * Name of expanded state of expandable element.
   */
  public const EXPANDED = 'expanded';

  /**
   * Name of collapsed state of expandable element.
   */
  public const COLLAPSED = 'collapsed';

  /**
   * Current instance state name getter.
   *
   * @return string
   *   State name.
   */
  public function getState(): string;

  /**
   * Set state to checked.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setChecked(WatcherInterface ...$watchers): static;

  /**
   * Set collapsed state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setCollapsed(WatcherInterface ...$watchers): static;

  /**
   * Set disabled state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setDisabled(WatcherInterface ...$watchers): static;

  /**
   * Set enabled state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setEnabled(WatcherInterface ...$watchers): static;

  /**
   * Set expanded state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setExpanded(WatcherInterface ...$watchers): static;

  /**
   * Set invalid state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setInvalid(WatcherInterface ...$watchers): static;

  /**
   * Set invisible state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setInvisible(WatcherInterface ...$watchers): static;

  /**
   * Set irrelevant state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setIrrelevant(WatcherInterface ...$watchers): static;

  /**
   * Set optional state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setOptional(WatcherInterface ...$watchers): static;

  /**
   * Set readonly state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setReadonly(WatcherInterface ...$watchers): static;

  /**
   * Set readwrite state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setReadwrite(WatcherInterface ...$watchers): static;

  /**
   * Set relevant state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setRelevant(WatcherInterface ...$watchers): static;

  /**
   * Set required state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setRequired(WatcherInterface ...$watchers): static;

  /**
   * Set touched state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setTouched(WatcherInterface ...$watchers): static;

  /**
   * Set unchecked state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setUnchecked(WatcherInterface ...$watchers): static;

  /**
   * Set untouched state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setUntouched(WatcherInterface ...$watchers): static;

  /**
   * Set valid state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setValid(WatcherInterface ...$watchers): static;

  /**
   * Set visible state.
   *
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setVisible(WatcherInterface ...$watchers): static;

  /**
   * Set custom state.
   *
   * Can be used for state which handled by 3rd party script.
   *
   * @param string $state
   *   Custom state name.
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setCustomState(string $state, WatcherInterface ...$watchers): static;

}
