<?php

namespace Drupal\jsonapi\Form;

use Drupal\Core\Form\ConfigFormBase;
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
      '#config_target' => [
        'target' => 'jsonapi.settings:read_only',
        'load_callback' => '::loadReadOnlyValue',
        'save_callback' => '::saveReadOnlyValue',
      ],
      '#description' => $this->t('Warning: Only enable all operations if the site requires it. <a href=":docs">Learn more about securing your site with JSON:API.</a>', [':docs' => 'https://www.drupal.org/docs/8/modules/jsonapi/security-considerations']),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Transformation callback for the read_only config value.
   *
   * @param bool $read_only
   *   The value to transform.
   *
   * @return string
   *   The transformed value.
   */
  public function loadReadOnlyValue(bool $read_only): string {
    return $read_only ? 'r' : 'rw';
  }

  /**
   * Transformation callback for the read_only config value.
   *
   * @param string $read_only
   *   The value to transform.
   *
   * @return bool
   *   The transformed value.
   */
  public function saveReadOnlyValue(string $value): bool {
    return $value === 'r';
  }

}
