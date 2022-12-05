<?php

namespace Drupal\Core\Form\States;

/**
 * State class. Helps define condition for the element.
 */
class State implements StateInterface {

  /**
   * State constructor.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\WatcherInterface[] $watchers
   *   Watcher instances storage.
   */
  public function __construct(
    protected string $state = '',
    protected array $watchers = [],
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getState(): string {
    return $this->state;
  }

  /**
   * {@inheritdoc}
   */
  public function setChecked(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::CHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCollapsed(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::COLLAPSED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setDisabled(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::DISABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::ENABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setExpanded(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::EXPANDED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvalid(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::INVALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvisible(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::INVISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setIrrelevant(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::IRRELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setOptional(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::OPTIONAL, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadonly(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::READONLY, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadwrite(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::READWRITE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRelevant(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::RELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::REQUIRED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setTouched(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::TOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUnchecked(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::UNCHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUntouched(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::UNTOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setValid(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::VALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setVisible(WatcherInterface ...$watchers): static {
    return $this->setStateRaw(static::VISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomState(string $state, WatcherInterface ...$watchers): static {
    return $this->setStateRaw($state, ...$watchers);
  }

  /**
   * Base state setter method.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\WatcherInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  protected function setStateRaw(string $state, WatcherInterface ...$watchers): static {
    $this->state = $state;
    $this->watchers += $watchers;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $array = [];
    foreach ($this->watchers as $watcher) {
      $selector = $watcher->getSelector();
      $array[$selector] = ($array[$selector] ?? []) + $watcher->toArray();
    }
    return $array;
  }

}
