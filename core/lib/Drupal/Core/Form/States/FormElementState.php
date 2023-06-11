<?php

namespace Drupal\Core\Form\States;

/**
 * State class. Helps define condition for the element.
 */
class FormElementState implements FormElementStateInterface {

  /**
   * State constructor.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\FormElementWatchableInterface[] $watchers
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
  public function setChecked(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::CHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCollapsed(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::COLLAPSED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setDisabled(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::DISABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::ENABLED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setExpanded(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::EXPANDED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvalid(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::INVALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setInvisible(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::INVISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setIrrelevant(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::IRRELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setOptional(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::OPTIONAL, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadonly(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::READONLY, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setReadwrite(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::READWRITE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRelevant(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::RELEVANT, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::REQUIRED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setTouched(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::TOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUnchecked(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::UNCHECKED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setUntouched(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::UNTOUCHED, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setValid(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::VALID, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setVisible(FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw(static::VISIBLE, ...$watchers);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomState(string $state, FormElementWatchableInterface ...$watchers): static {
    return $this->setStateRaw($state, ...$watchers);
  }

  /**
   * Base state setter method.
   *
   * @param string $state
   *   State name.
   * @param \Drupal\Core\Form\States\FormElementWatchableInterface ...$watchers
   *   Watcher instances.
   *
   * @return $this
   *   State instance.
   */
  protected function setStateRaw(string $state, FormElementWatchableInterface ...$watchers): static {
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
      if ($watcher instanceof FormElementWatcherConditionGroupInterface) {
        if ($key !== 0) {
          $array[] = $watcher->getConditionOperator();
        }
        $array[] = $watcher->toArray();
      }
      if ($watcher instanceof FormElementWatcherInterface) {
        $selector = $watcher->getSelector();
        $array[$selector] = ($array[$selector] ?? []) + $watcher->toArray();
      }
    }
    return $array;
  }

}
