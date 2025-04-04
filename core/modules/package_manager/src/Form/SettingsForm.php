<?php

declare(strict_types=1);

namespace Drupal\package_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure Package Manager settings.
 *
 * @internal
 *   This is an internal part of Package Manager and may be changed or removed
 *   at any time without warning. External code should not interact with this
 *   class.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'package_manager_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['package_manager.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $value_or_null = fn (string $value): ?string => trim($value) ?: NULL;

    $form['executables']['composer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to Composer'),
      '#description' => $this->t('The full path to the Composer executable (usually named <code>composer</code> or <code>composer.phar</code>. Leave blank to auto-detect.'),
      '#config_target' => new ConfigTarget(
        'package_manager.settings',
        'executables.composer',
        toConfig: $value_or_null,
      ),
    ];
    $form['executables']['rsync'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to rsync'),
      '#description' => $this->t('The full path to the <code>rsync</code> executable. Leave blank to auto-detect.'),
      '#config_target' => new ConfigTarget(
        'package_manager.settings',
        'executables.rsync',
        toConfig: $value_or_null,
      ),
    ];
    return parent::buildForm($form, $form_state);
  }

}
