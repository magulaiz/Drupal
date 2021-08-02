<?php

namespace Drupal\Core\Form\States;

/**
 * Interface StatesElementConditionsInterface.
 *
 * @package Drupal\Core\Form.
 */
interface StateInterface extends StatesAndConditionsInterface {
  const ENABLED = 'enabled';
  const DISABLED = 'disabled';
  const REQUIRED = 'required';
  const OPTIONAL = 'optional';
  const VISIBLE = 'visible';
  const INVISIBLE = 'invisible';
  const CHECKED = 'checked';
  const UNCHECKED = 'unchecked';
  const EXPANDED = 'expanded';
  const COLLAPSED = 'collapsed';
}
