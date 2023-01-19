<?php

namespace Drupal\Core\Form;

use Drupal\Core\Form\States\FormElementStatesBuilderInterface;

/**
 * States builder provider interface.
 */
interface FormStatesBuilderProviderInterface {

  /**
   * States builder getter.
   *
   * @return \Drupal\Core\Form\States\FormElementStatesBuilderInterface
   *   Instance of States builder
   */
  public function getStatesBuilder(): FormElementStatesBuilderInterface;

}
