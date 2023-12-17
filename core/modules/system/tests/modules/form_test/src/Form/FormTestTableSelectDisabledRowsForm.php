<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Builds a form to test table select with disabled rows.
 *
 * @internal
 */
class FormTestTableSelectDisabledRowsForm extends FormTestTableSelectFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_form_test_tableselect_disabled_rows_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $test_action = NULL) {
    $multiple = ['multiple-true' => TRUE, 'multiple-false' => FALSE][$test_action];
    $form = $this->tableselectFormBuilder($form, $form_state, [
      '#multiple' => $multiple,
      '#js_select' => TRUE,
      '#ajax' => NULL,
    ]);

    // Disable the second row.
    $form['tableselect']['#options']['row2']['#disabled'] = TRUE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
