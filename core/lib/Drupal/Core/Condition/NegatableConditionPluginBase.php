<?php

namespace Drupal\Core\Condition;

/**
 * Extends for condition plugins that allow negation.
 *
 * @see \Drupal\Core\Condition\\ConditionPluginBase
 * @see \Drupal\Core\Condition\NegatableConditionInterface
 * @see \Drupal\Core\Condition\ConditionManager
 *
 * @ingroup plugin_api
 */
abstract class NegatableConditionPluginBase extends ConditionPluginBase implements NegatableConditionInterface {

  /**
   * {@inheritdoc}
   */
  public function evaluateIsNegated(bool $evaluation_result): bool {
    return $this->isNegated() ? !$evaluation_result : $evaluation_result;
  }

}
