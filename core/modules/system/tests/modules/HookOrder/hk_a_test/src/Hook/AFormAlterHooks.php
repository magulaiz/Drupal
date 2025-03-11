<?php

declare(strict_types = 1);

namespace Drupal\hk_a_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order;
use Drupal\Core\Hook\OrderAfter;

class AFormAlterHooks {

  #[Hook('form_alter')]
  public function formAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_myform_alter')]
  public function myFormAlter(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_alter', order: new OrderAfter(modules: ['hk_b_test']))]
  public function formAlterAfterB(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_myform_alter', order: new OrderAfter(modules: ['hk_b_test']))]
  public function myFormAlterAfterB(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_alter', order: new OrderAfter(modules: ['hk_b_test'], extraTypes: ['form_myform_alter']))]
  public function formAlterAfterBExtra(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

  #[Hook('form_myform_alter', order: new OrderAfter(modules: ['hk_b_test'], extraTypes: ['form_alter']))]
  public function myFormAlterAfterBExtra(array &$form): void {
    $form['#calls'][] = __METHOD__;
  }

}
