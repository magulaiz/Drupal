<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class FormInComponent extends ControllerBase {

  /**
   * Returns a form in a component.
   */
  public function build(): array {
    $form = $this->formBuilder()->getForm(\Drupal\core_sdc_form\Form\FormInComponent::class);
    return [
      '#type' => 'component',
      '#component' => 'navigation:badge',
      '#slots' => [
        'label' => $form,
      ],
    ];
  }

}
