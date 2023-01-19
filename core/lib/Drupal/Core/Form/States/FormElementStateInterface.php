<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface FormElementStateInterface extends FormElementBaseStateInterface {

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
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setChecked(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set collapsed state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setCollapsed(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set disabled state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setDisabled(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set enabled state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setEnabled(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set expanded state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setExpanded(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set invalid state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setInvalid(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set invisible state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setInvisible(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set irrelevant state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setIrrelevant(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set optional state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setOptional(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set readonly state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setReadonly(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set readwrite state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setReadwrite(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set relevant state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setRelevant(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set required state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setRequired(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set touched state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setTouched(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set unchecked state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setUnchecked(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set untouched state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setUntouched(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set valid state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setValid(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set visible state.
   *
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setVisible(FormElementWatcherInterface ...$watchers): static;

  /**
   * Set custom state.
   *
   * Can be used for state which handled by 3rd party script.
   *
   * @param string $state
   *   Custom state name.
   * @param \Drupal\Core\Form\States\FormElementWatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  public function setCustomState(string $state, FormElementWatcherInterface ...$watchers): static;

}
