<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * The following states may be used in remote conditions.
 *
 * @package Drupal\Core\Form.
 *
 * @see \Drupal\Core\Form\FormHelper::processStates()
 */
interface ConditionInterface extends WeaklySupportedStatesInterface, CheckedStatesInterface, CollapsibleStatesInterface {

  /**
   * State of the form element which not contains any value.
   */
  public const EMPTY = 'empty';

  /**
   * State of the form element which contains the value.
   */
  public const FILLED = 'filled';

  /**
   * State used for condition to check equality comparing to the given value.
   *
   * When referencing select lists and radio buttons in remote conditions,
   * a 'value' condition must be used:
   * ```
   * '#states' => [
   *   // Show the settings if 'bar' has been selected for 'foo'.
   *   StateInterface::VISIBLE => [
   *     ':input[name="foo"]' => [ConditionInterface::VALUE => 'bar'],
   *   ],
   * ],
   * ```
   */
  public const VALUE = 'value';

}
