<?php

namespace Drupal\file_test\Form;

use Drupal\Core\File\FileExists;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides methods common for FileTestForm + FileTestSaveUploadFromForm.
 */
abstract class FileTestFormBase extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['file_test_replace'] = [
      '#type' => 'select',
      '#title' => $this->t('Replace existing image'),
      '#options' => [
        FileExists::Rename => $this->t('Appends number until name is unique'),
        FileExists::Replace => $this->t('Replace the existing file'),
        FileExists::Error => $this->t('Fail with an error'),
      ],
      '#default_value' => FileExists::Rename,
    ];

    $form['file_subdir'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subdirectory for test file'),
      '#default_value' => '',
    ];

    $form['extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed extensions.'),
      '#default_value' => '',
    ];

    $form['allow_all_extensions'] = [
      '#title' => $this->t('Allow all extensions?'),
      '#type' => 'radios',
      '#options' => [
        'false' => $this->t('No'),
        'empty_array' => $this->t('Empty array'),
        'empty_string' => $this->t('Empty string'),
      ],
      '#default_value' => 'false',
    ];

    $form['is_image_file'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is this an image file?'),
      '#default_value' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

}
