<?php

namespace Drupal\menu_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the menu filter form.
 *
 * @internal
 */
class MenuFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_filter_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $keys
   *   The filter value.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $keys = NULL) {
    $form['basic'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter menus by title'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['basic']['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Title'),
      '#title_display' => 'invisible',
      '#default_value' => $keys,
      '#maxlength' => 128,
      '#size' => 25,
    ];
    $form['basic']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    if ($keys) {
      $form['basic']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::resetForm'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.menu.collection', [], [
      'query' => ['search' => trim($form_state->getValue('filter'))],
    ]);
  }

  /**
   * Resets the filter selections.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.menu.collection');
  }

}
