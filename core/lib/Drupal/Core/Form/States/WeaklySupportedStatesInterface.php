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
  const RELEVANT = 'relevant';

  /**
   * State defines if element is irrelevant.
   */
  const IRRELEVANT = 'irrelevant';

  /**
   * States defines if element is valid.
   */
  const VALID = 'valid';

  /**
   * States defines if element is invalid.
   */
  const INVALID = 'invalid';

  /**
   * States defines if element is touched.
   */
  const TOUCHED = 'touched';

  /**
   * States defines if element is untouched.
   */
  const UNTOUCHED = 'untouched';

  /**
   * States defines if element is readwrite.
   */
  const READWRITE = 'readwrite';

  /**
   * States defines if element is readonly.
   */
  const READONLY = 'readonly';

}
