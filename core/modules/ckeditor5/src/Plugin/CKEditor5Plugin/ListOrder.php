<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Form\FormStateInterface;

/**
 * CKEditor 5 List Order plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ListOrder extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['reverse' => TRUE, 'startIndex' => TRUE];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['reverse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to reverse a numbered list'),
      '#default_value' => $this->configuration['reverse'],
    ];
    $form['startIndex'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to specify the start index of a list'),
      '#default_value' => $this->configuration['startIndex'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_value = $form_state->getValue('reverse');
    $form_state->setValue('reverse', (bool) $form_value);
    $form_value = $form_state->getValue('startIndex');
    $form_state->setValue('startIndex', (bool) $form_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['reverse'] = $form_state->getValue('reverse');
    $this->configuration['startIndex'] = $form_state->getValue('startIndex');
  }

}
