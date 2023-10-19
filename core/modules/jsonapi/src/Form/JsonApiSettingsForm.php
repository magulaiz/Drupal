<?php

namespace Drupal\jsonapi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure JSON:API settings for this site.
 *
 * @internal
 */
class JsonApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jsonapi_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['jsonapi.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['read_only'] = [
      '#type' => 'radios',
      '#title' => $this->t('Allowed operations'),
      '#options' => [
        'r' => $this->t('Accept only JSON:API read operations.'),
        'rw' => $this->t('Accept all JSON:API create, read, update, and delete operations.'),
      ],
      '#config_target' => new ConfigTarget(
        'jsonapi.settings',
        'read_only',
        static::class . '::loadReadOnlyFromConfig',
        static::class . '::transformReadOnlyForStorage',
      ),
      '#description' => $this->t('Warning: Only enable all operations if the site requires it. <a href=":docs">Learn more about securing your site with JSON:API.</a>', [':docs' => 'https://www.drupal.org/docs/8/modules/jsonapi/security-considerations']),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Prepares the 'read_only' config value to be displayed by the form.
   *
   * @param bool $value
   *   The 'read_only' value stored in config.
   *
   * @return string
   *   Either 'r' or 'rw', depending on the stored value.
   */
  public static function loadReadOnlyFromConfig(bool $value): string {
    return $value ? 'r' : 'rw';
  }

  /**
   * Prepares the submitted 'read_only' value to be stored in config.
   *
   * @param string $value
   *   The submitted 'read_only' value.
   *
   * @return bool
   *   The value to store in the config 'read_only' property.
   */
  public static function transformReadOnlyForStorage(string $value): bool {
    return $value === 'r';
  }

}
