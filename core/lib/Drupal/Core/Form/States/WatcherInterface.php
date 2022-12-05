<?php

namespace Drupal\Core\Form\States;

/**
 * Interface WatcherStateInterface.
 *
 * @package Drupal\Core\Form.
 */
interface WatcherInterface extends BaseStateInterface {

  public const EMPTY = 'empty';

  public const FILLED = 'filled';

  public const CHECKED = 'checked';

  public const UNCHECKED = 'unchecked';

  public const EXPANDED = 'expanded';

  public const COLLAPSED = 'collapsed';

  public const VALUE = 'value';

  /**
   *
   */
  public function getSelector(): string;

  /**
   *
   */
  public function isChecked(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isCollapsed(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isExpanded(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isInvalid(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isIrrelevant(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isReadonly(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isReadwrite(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isRelevant(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isTouched(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isUnchecked(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isUntouched(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isValid(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isEmpty(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function isFilled(bool $condition = TRUE, bool $negate = FALSE): static;

  /**
   *
   */
  public function valueEqualTo($value, bool $negate = FALSE): static;

  /**
   *
   */
  public function setCustomCondition(string $state, $condition): static;


}
