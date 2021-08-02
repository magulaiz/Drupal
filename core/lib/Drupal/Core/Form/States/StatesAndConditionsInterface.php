<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface StatesAndConditionsInterface {
  const RELEVANT = 'relevant';
  const IRRELEVANT = 'irrelevant';
  const VALID = 'valid';
  const INVALID = 'invalid';
  const TOUCHED = 'touched';
  const UNTOUCHED = 'untouched';
  const READWRITE = 'readwrite';
  const READONLY = 'readonly';
}
