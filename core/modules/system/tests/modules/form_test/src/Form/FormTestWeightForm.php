<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form for testing form element weights.
 *
 * @internal
 *
 * @see \Drupal\Tests\system\Functional\Form\ElementTest::testFormDescriptions()
 */
class FormTestWeightForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form_test_weight';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $alter = FALSE): array {
    $form['form_textfield_test_1'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield 1',
      '#value' => '1',
      '#weight' => 20,
    ];

    $form['form_textfield_test_test_2'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield 2',
      '#value' => '2',
      '#weight' => 30,
    ];

    $form['form_textfield_test_test_3'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield 2',
      '#value' => '3',
      '#weight' => 10,
    ];

    // For one of the test runs, set a variable to enable a form alter hook.
    if ($alter) {
      $form['#enable_form_alter'] = TRUE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // The test that uses this form does not submit the form so this is empty.
  }

}
