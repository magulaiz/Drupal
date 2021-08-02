<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface ConditionInterface extends StatesAndConditionsInterface {
  const EMPTY = 'empty';
  const FILLED = 'filled';
  const CHECKED = 'checked';
  const UNCHECKED = 'unchecked';
  const EXPANDED = 'expanded';
  const COLLAPSED = 'collapsed';
  const VALUE = 'value';
}
