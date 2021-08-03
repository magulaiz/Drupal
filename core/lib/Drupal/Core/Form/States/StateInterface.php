<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * The following states may be applied to an element.
 *
 * @package Drupal\Core\Form.
 *
 * @see \Drupal\Core\Form\FormHelper::processStates()
 */
interface StateInterface extends WeaklySupportedStatesInterface, CheckedStatesInterface, CollapsibleStatesInterface {

  /**
   * State defines enabled possibility to input value to the form element.
   */
  public const ENABLED = 'enabled';

  /**
   * State defines disabled possibility to input value to the form element.
   */
  public const DISABLED = 'disabled';

  /**
   * State defines that element is required.
   */
  public const REQUIRED = 'required';

  /**
   * State defines that element is not required.
   */
  public const OPTIONAL = 'optional';

  /**
   * State defines that element is visible.
   */
  public const VISIBLE = 'visible';

  /**
   * State defines that element is invisible.
   */
  public const INVISIBLE = 'invisible';

}
