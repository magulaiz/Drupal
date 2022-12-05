<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface BaseStateInterface {

  /**
   * Name of relevant state.
   */
  public const RELEVANT = 'relevant';

  /**
   * Name of irrelevant state.
   */
  public const IRRELEVANT = 'irrelevant';

  /**
   * Name of valid state.
   */
  public const VALID = 'valid';

  /**
   * Name of invalid state.
   */
  public const INVALID = 'invalid';

  /**
   * Name of touched state.
   */
  public const TOUCHED = 'touched';

  /**
   * Name of untouched state.
   */
  public const UNTOUCHED = 'untouched';

  /**
   * Name of readwrite state.
   */
  public const READWRITE = 'readwrite';

  /**
   * Name of readonly state.
   */
  public const READONLY = 'readonly';

  /**
   * Convert instance to array suitable for builder instance.
   *
   * @return array
   *   Part of states array.
   */
  public function toArray(): array;

}
