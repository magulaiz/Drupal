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
   * @param \Drupal\Core\Form\States\WatchableInterface[] $watchers
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
  public function setChecked(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::CHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCollapsed(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::COLLAPSED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setDisabled(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::DISABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::ENABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setExpanded(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::EXPANDED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvalid(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::INVALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvisible(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::INVISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setIrrelevant(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::IRRELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setOptional(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::OPTIONAL, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadonly(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::READONLY, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadwrite(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::READWRITE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRelevant(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::RELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::REQUIRED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setTouched(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::TOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUnchecked(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::UNCHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUntouched(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::UNTOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setValid(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::VALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setVisible(WatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::VISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomState(string $state, WatchableInterface ...$watchers): static {
    return $this->setStateRaw($state, ...$watchers);
  }

  /**
   * Base state setter method.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\WatchableInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  protected function setStateRaw(string $state, WatchableInterface ...$watchers): static {
    $this->state = $state;
    $this->watchers += $watchers;
    return $this;
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
