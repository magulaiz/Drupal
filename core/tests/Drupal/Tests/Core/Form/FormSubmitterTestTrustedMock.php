<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;
use PHPUnit\Framework\ExpectationFailedException;

class FormSubmitterTestTrustedMock extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'FormSubmitterTestTrustedMock';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Function used in the mocking process of this test.
   */
  #[TrustedCallback]
  public function submit_handler(array &$form, FormStateInterface $form_state): void {
    if (isset($form['#submit_handler_called'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormSubmitterTestTrustedMock::submit_handler called more than once.');
    }
    $form['#submit_handler_called'] = TRUE;
  }

  /**
   * Function used in the mocking process of this test.
   */
  #[TrustedCallback]
  public function hash_submit(array &$form, FormStateInterface $form_state): void {
    if (isset($form['#hash_submit_called'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormSubmitterTestTrustedMock::hash_submit called more than once.');
    }
    $form['#hash_submit_called'] = TRUE;
  }

  /**
   * Function used in the mocking process of this test.
   */
  #[TrustedCallback]
  public function simple_string_submit(array &$form, FormStateInterface $form_state): void {
    if (isset($form['#simple_string_submit_called'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormSubmitterTestTrustedMock::simple_string_submit called more than once.');
    }
    $form['#simple_string_submit_called'] = TRUE;
  }

}
