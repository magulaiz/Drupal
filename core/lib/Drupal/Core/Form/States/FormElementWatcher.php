<?php

namespace Drupal\Core\Form\States;

/**
 * Watcher class. Helps to build remote condition for the state instance.
 */
class FormElementWatcher implements FormElementWatcherInterface {

  /**
   * Flags pointing for multiple condition.
   *
   * @var bool
   */
  protected bool $isMultiple = FALSE;

  /**
   * Watcher constructor.
   *
   * @param string $selector
   *   Selector value of remote condition element.
   * @param array $conditionStates
   *   Remote condition storage.
   */
  public function __construct(
    protected string $selector,
    protected array $conditionStates = [],
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getSelector(): string {
    return $this->selector;
  }

  /**
   * {@inheritdoc}
   */
  public function isChecked(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::CHECKED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isCollapsed(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::COLLAPSED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isExpanded(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::EXPANDED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isInvalid(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::INVALID, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isIrrelevant(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::IRRELEVANT, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isReadonly(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::READONLY, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isReadwrite(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::READWRITE, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isRelevant(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::RELEVANT, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isTouched(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::TOUCHED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isUnchecked(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::UNCHECKED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isUntouched(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::UNTOUCHED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::VALID, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::EMPTY, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function isFilled(bool $condition = TRUE, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::FILLED, $condition, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function valueEqualTo(mixed $value, bool $negate = FALSE): static {
    return $this->setWatcherRaw(static::VALUE, $value, $negate);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomCondition(string $state, $condition): static {
    return $this->setWatcherRaw($state, $condition);
  }

  /**
   * Base Watcher setter method.
   */
  protected function setWatcherRaw(string $state, $condition, bool $negate = FALSE): static {
    $state = $negate ? '!' . $state : $state;
    if (!$this->isMultiple) {
      if (empty($this->conditionStates[$state])) {
        $this->conditionStates[$state] = $condition;
        return $this;
      }
      $stored = $this->conditionStates;
      $this->conditionStates = [];
      foreach ($stored as $stored_state => $stored_condition) {
        $this->conditionStates[] = [$stored_state => $stored_condition];
      }
      unset($stored);
      $this->isMultiple = TRUE;
    }
    $this->conditionStates[] = [$state => $condition];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    return $this->conditionStates;
  }

}
