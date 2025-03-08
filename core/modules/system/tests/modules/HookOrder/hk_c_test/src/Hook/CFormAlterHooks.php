<?php

declare(strict_types=1);

namespace Drupal\hk_c_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class CFormAlterHooks {

  #[Hook('form_alter')]
  public function formAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_myform_alter')]
  public function myFormAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

}
