<?php

namespace Drupal\views_test_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Simple form page callback to test the view element.
 *
 * @internal
 */
class ViewsTestDataElementForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'views_test_data_element_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['view'] = [
      '#type' => 'view',
      '#name' => 'test_view_embed',
      '#display_id' => 'default',
      '#arguments' => [25],
      '#embed' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
