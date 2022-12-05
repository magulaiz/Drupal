<?php

namespace Drupal\Core\Form;

use Drupal\Core\Form\States\StatesBuilderInterface;

/**
 * States builder provider interface.
 */
interface FormStatesBuilderProviderInterface {

  /**
   * States builder getter.
   *
   * @return \Drupal\Core\Form\States\StatesBuilderInterface
   *   Instance of States builder
   */
  public function getStatesBuilder(): StatesBuilderInterface;

}
