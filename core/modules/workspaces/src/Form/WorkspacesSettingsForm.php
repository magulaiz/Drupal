<?php

declare(strict_types=1);

namespace Drupal\workspaces\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Workspaces.
 */
final class WorkspacesSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'workspaces_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['allow_parallel'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow parallel Workspaces'),
      '#description' => $this->t('Users will be able to edit content across multiple workspaces.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('workspaces.settings')
      ->setData($form_state->cleanValues()->getValues())
      ->save();
    $this->messenger()->addStatus($this->t('The settings have been saved.'));
  }

}
