<?php

namespace Drupal\batch_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Generate form of id batch_test_chained_form.
 *
 * @internal
 */
class BatchTestChainedForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_test_chained_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // This value is used to test that $form_state persists through batched
    // submit handlers.
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => 'Value',
      '#default_value' => 1,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    $form['#submit'] = [
      'Drupal\batch_test\Form\BatchTestChainedForm::batchTestChainedFormSubmit1',
      'Drupal\batch_test\Form\BatchTestChainedForm::batchTestChainedFormSubmit2',
      'Drupal\batch_test\Form\BatchTestChainedForm::batchTestChainedFormSubmit3',
      'Drupal\batch_test\Form\BatchTestChainedForm::batchTestChainedFormSubmit4',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Form submission handler #1 for batch_test_chained_form.
   */
  public static function batchTestChainedFormSubmit1($form, FormStateInterface $form_state) {
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack(NULL, TRUE);
    \Drupal::moduleHandler()->loadInclude('batch_test', 'inc', 'batch_test.callbacks');

    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('submit handler 1');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('value = ' . $form_state->getValue('value'));

    $value = &$form_state->getValue('value');
    $value++;
    batch_set(_batch_test_batch_1());

    $form_state->setRedirect('batch_test.redirect');
  }

  /**
   * Form submission handler #2 for batch_test_chained_form.
   */
  public static function batchTestChainedFormSubmit2($form, FormStateInterface $form_state) {
    \Drupal::moduleHandler()->loadInclude('batch_test', 'inc', 'batch_test.callbacks');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('submit handler 2');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('value = ' . $form_state->getValue('value'));

    $value = &$form_state->getValue('value');
    $value++;
    batch_set(_batch_test_batch_2());

    $form_state->setRedirect('batch_test.redirect');
  }

  /**
   * Form submission handler #3 for batch_test_chained_form.
   */
  public static function batchTestChainedFormSubmit3($form, FormStateInterface $form_state) {
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('submit handler 3');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('value = ' . $form_state->getValue('value'));

    $value = &$form_state->getValue('value');
    $value++;

    $form_state->setRedirect('batch_test.redirect');
  }

  /**
   * Form submission handler #4 for batch_test_chained_form.
   */
  public static function batchTestChainedFormSubmit4($form, FormStateInterface $form_state) {
    \Drupal::moduleHandler()->loadInclude('batch_test', 'inc', 'batch_test.callbacks');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('submit handler 4');
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack('value = ' . $form_state->getValue('value'));

    $value = &$form_state->getValue('value');
    $value++;
    batch_set(_batch_test_batch_3());

    $form_state->setRedirect('batch_test.redirect');
  }

}
