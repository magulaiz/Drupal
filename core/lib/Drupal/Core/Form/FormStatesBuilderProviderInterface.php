<?php

namespace Drupal\Core\Form;

use Drupal\Core\Form\States\StatesBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

interface FormStatesBuilderProviderInterface {

  public function getStatesBuilder(): StatesBuilderInterface;

}
