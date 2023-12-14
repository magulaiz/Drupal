<?php

namespace Drupal\Core\Condition;

/**
 * An interface for condition plugins that allow negation.
 *
 * @see \Drupal\Core\Condition\ConditionInterface
 *
 * @ingroup plugin_api
 */
interface NegatableConditionInterface extends ConditionInterface {

  /**
   * Negate the condition result.
   *
   * @param bool $evaluation_result
   *   Condition evaluation result boolean.
   *
   * @return bool
   *   TRUE if the result is FALSE, FALSE otherwise.
   */
  public function evaluateIsNegated($evaluation_result);

}
