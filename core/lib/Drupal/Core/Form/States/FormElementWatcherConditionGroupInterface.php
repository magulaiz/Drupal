<?php

namespace Drupal\Core\Form\States;

/**
 * Watcher condition group interface.
 */
interface FormElementWatcherConditionGroupInterface extends FormElementWatchableInterface {

  /**
   * AND condition operator.
   */
  public const AND = 'and';

  /**
   * OR condition operator.
   */
  public const OR = 'or';

  /**
   * XOR condition operator.
   */
  public const XOR = 'xor';

  /**
   * To array converter method.
   */
  public function toArray(): array;

  /**
   * Condition operator getter.
   */
  public function getConditionOperator(): string;

}
