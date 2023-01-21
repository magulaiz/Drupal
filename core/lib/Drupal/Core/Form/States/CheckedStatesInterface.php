<?php

namespace Drupal\Core\Form\States;

/**
 * Interface CheckedStatesInterface.
 *
 * States of the checkbox/radio elements.
 *
 * @package Drupal\Core\Form.
 *
 * @see \Drupal\Core\Form\FormHelper::processStates()
 */
interface CheckedStatesInterface {

  /**
   * State of the checkbox/radio element which is checked.
   */
  public const CHECKED = 'checked';

  /**
   * State of the checkbox/radio elements which is unchecked.
   */
  public const UNCHECKED = 'unchecked';

}
