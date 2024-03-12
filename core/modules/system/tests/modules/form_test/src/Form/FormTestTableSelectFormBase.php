<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base class for tableselect forms.
 *
 * @internal
 */
abstract class FormTestTableSelectFormBase extends FormBase {

  /**
   * Build a form to test the tableselect element.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param $element_properties
   *   An array of element properties for the tableselect element.
   *
   * @return array
   *   A form with a tableselect element and a submit button.
   */
  public function tableselectFormBuilder($form, FormStateInterface $form_state, $element_properties) {
    [$header, $options] = $this->formTestTableselectGetData();

    $form['tableselect'] = $element_properties;

    $form['tableselect'] += [
      '#prefix' => '<div id="tableselect-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#multiple' => FALSE,
      '#empty' => t('Empty text.'),
      '#ajax' => [
        'callback' => 'form_test_tableselect_ajax_callback',
        'wrapper' => 'tableselect-wrapper',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    return $form;
  }

  /**
   * Create a header and options array. Helper function for callbacks.
   */
  public function formTestTableselectGetData() {
    $header = [
      'one' => t('One'),
      'two' => t('Two'),
      'three' => t('Three'),
      'four' => t('Four'),
    ];

    $options['row1'] = [
      'title' => ['data' => ['#title' => t('row1')]],
      'one' => 'row1col1',
      'two' => t('row1col2'),
      'three' => t('row1col3'),
      'four' => t('row1col4'),
    ];

    $options['row2'] = [
      'title' => ['data' => ['#title' => t('row2')]],
      'one' => 'row2col1',
      'two' => t('row2col2'),
      'three' => t('row2col3'),
      'four' => t('row2col4'),
    ];

    $options['row3'] = [
      'title' => ['data' => ['#title' => t('row3')]],
      'one' => 'row3col1',
      'two' => t('row3col2'),
      'three' => t('row3col3'),
      'four' => t('row3col4'),
    ];

    return [$header, $options];
  }

}
