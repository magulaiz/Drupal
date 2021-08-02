<?php

namespace Drupal\Core\Form\States;

/**
 * Interface CollapsibleStatesInterface.
 *
 * States of the collapsible elements.
 *
 * @package Drupal\Core\Form.
 *
 * @see \Drupal\Core\Form\FormHelper::processStates()
 */
interface CollapsibleStatesInterface {

  /**
   * State of the collapsible element (i.e. details) which in is expanded.
   */
  public const EXPANDED = 'expanded';

  /**
   * State of the collapsible element (i.e. details) which in is collapsed.
   */
  public const COLLAPSED = 'collapsed';

}
