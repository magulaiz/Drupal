<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * The following states exist for both elements and remote conditions, but are
 * not fully implemented and may not change anything on the element.
 *
 * @package Drupal\Core\Form.
 *
 * @see \Drupal\Core\Form\FormHelper::processStates()
 */
interface WeaklySupportedStatesInterface {

  /**
   * State defines if element is relevant.
   */
  public const RELEVANT = 'relevant';

  /**
   * State defines if element is irrelevant.
   */
  public const IRRELEVANT = 'irrelevant';

  /**
   * States defines if element is valid.
   */
  public const VALID = 'valid';

  /**
   * States defines if element is invalid.
   */
  public const INVALID = 'invalid';

  /**
   * States defines if element is touched.
   */
  public const TOUCHED = 'touched';

  /**
   * States defines if element is untouched.
   */
  public const UNTOUCHED = 'untouched';

  /**
   * States defines if element is readwrite.
   */
  public const READWRITE = 'readwrite';

  /**
   * States defines if element is readonly.
   */
  public const READONLY = 'readonly';

}
