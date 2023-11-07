<?php

namespace Drupal\locale\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigMultiTarget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure locale settings for this site.
 *
 * @internal
 */
class LocaleSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'locale_translate_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['locale.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('locale.settings');

    $form['update_interval_days'] = [
      '#type' => 'radios',
      '#title' => $this->t('Check for updates'),
      '#config_target' => 'locale.settings:translation.update_interval_days',
      '#options' => [
        '0' => $this->t('Never (manually)'),
        '7' => $this->t('Weekly'),
        '30' => $this->t('Monthly'),
      ],
      '#description' => $this->t('Select how frequently you want to check for new interface translations for your currently installed modules and themes. <a href=":url">Check updates now</a>.', [':url' => Url::fromRoute('locale.check_translation')->toString()]),
    ];

    if ($directory = $config->get('translation.path')) {
      $description = $this->t('Translation files are stored locally in the  %path directory. You can change this directory on the <a href=":url">File system</a> configuration page.', ['%path' => $directory, ':url' => Url::fromRoute('system.file_system_settings')->toString()]);
    }
    else {
      $description = $this->t('Translation files will not be stored locally. Change the Interface translation directory on the <a href=":url">File system configuration</a> page.', [':url' => Url::fromRoute('system.file_system_settings')->toString()]);
    }
    $form['#translation_directory'] = $directory;
    $form['use_source'] = [
      '#type' => 'radios',
      '#title' => $this->t('Translation source'),
      '#config_target' => 'locale.settings:translation.use_source',
      '#options' => [
        LOCALE_TRANSLATION_USE_SOURCE_REMOTE_AND_LOCAL => $this->t('Drupal translation server and local files'),
        LOCALE_TRANSLATION_USE_SOURCE_LOCAL => $this->t('Local files only'),
      ],
      '#description' => $this->t('The source of translation files for automatic interface translation.') . ' ' . $description,
    ];

    $form['overwrite'] = [
      '#type' => 'radios',
      '#title' => $this->t('Import behavior'),
      '#options' => [
        LOCALE_TRANSLATION_OVERWRITE_NONE => $this->t("Don't overwrite existing translations."),
        LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED => $this->t('Only overwrite imported translations, customized translations are kept.'),
        LOCALE_TRANSLATION_OVERWRITE_ALL => $this->t('Overwrite existing translations.'),
      ],
      '#description' => $this->t('How to treat existing translations when automatically updating the interface translations.'),
      '#config_target' => new ConfigMultiTarget(
        'locale.settings',
        [
          'translation.overwrite_customized',
          'translation.overwrite_not_customized',
        ],
        static::class . '::fromOverwriteSettings',
        static::class . '::toOverwriteSettings',
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (empty($form['#translation_directory']) && $form_state->getValue('use_source') == LOCALE_TRANSLATION_USE_SOURCE_LOCAL) {
      $form_state->setErrorByName('use_source', $this->t('You have selected local translation source, but no <a href=":url">Interface translation directory</a> was configured.', [':url' => Url::fromRoute('system.file_system_settings')->toString()]));
    }
  }

  /**
   * Maps `locale.settings:translation.overwrite_*` to a UI form element value.
   *
   * @param bool $overwrite_customized
   *   The `locale.settings:translation.overwrite_customized` value.
   * @param bool $overwrite_not_customized
   *   The `locale.settings:translation.overwrite_not_customized` value.
   *
   * @return string
   *   One of:
   *   - LOCALE_TRANSLATION_OVERWRITE_ALL
   *   - LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED
   *   - LOCALE_TRANSLATION_OVERWRITE_NONE
   */
  public static function fromOverwriteSettings(bool $overwrite_customized, bool $overwrite_not_customized): string {
    if ($overwrite_not_customized == FALSE) {
      return LOCALE_TRANSLATION_OVERWRITE_NONE;
    }
    elseif ($overwrite_customized == TRUE) {
      return LOCALE_TRANSLATION_OVERWRITE_ALL;
    }
    else {
      return LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED;
    }
  }

  /**
   * Maps UI form element value to `locale.settings:translation.overwrite_*`.
   *
   * @param string $radio_option
   *   One of the 3 provided options:
   *   - LOCALE_TRANSLATION_OVERWRITE_ALL
   *   - LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED
   *   - LOCALE_TRANSLATION_OVERWRITE_NONE
   *
   * @return array
   *   The values for the 2 `locale.settings:translation.overwrite_*` property
   *   paths.
   */
  public static function toOverwriteSettings(string $radio_option): array {
    switch ($radio_option) {
      case LOCALE_TRANSLATION_OVERWRITE_ALL:
        return [
          'translation.overwrite_customized' => TRUE,
          'translation.overwrite_not_customized' => TRUE,
        ];

      case LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED:
        return [
          'translation.overwrite_customized' => FALSE,
          'translation.overwrite_not_customized' => TRUE,
        ];

      case LOCALE_TRANSLATION_OVERWRITE_NONE:
        return [
          'translation.overwrite_customized' => FALSE,
          'translation.overwrite_not_customized' => FALSE,
        ];

      default:
        throw new \Exception();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Invalidate the cached translation status when the configuration setting
    // of 'use_source' changes.
    if ($form['use_source']['#default_value'] != $form_state->getValue('use_source')) {
      locale_translation_clear_status();
    }

    parent::submitForm($form, $form_state);
  }

}
