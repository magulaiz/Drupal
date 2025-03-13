<?php

declare(strict_types=1);

namespace Drupal\hk_b_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hooks for testing ordering.
 */
class BFormAlterHooks {

  #[Hook('form_alter')]
  public function formAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_my_form_alter')]
  public function myFormAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

}
