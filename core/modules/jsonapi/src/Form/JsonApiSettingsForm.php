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
      '#config_target' => ['jsonapi.settings:read_only', '::transformReadOnly'],
      '#description' => $this->t('Warning: Only enable all operations if the site requires it. <a href=":docs">Learn more about securing your site with JSON:API.</a>', [':docs' => 'https://www.drupal.org/docs/8/modules/jsonapi/security-considerations']),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Transformation callback for the read_only config value.
   *
   * @param bool|string $read_only
   *   The value to transform.
   *
   * @return bool|string
   *   The transformed value.
   */
  public function transformReadOnly(bool|string $read_only) {
    if (is_bool($read_only)) {
      return $read_only ? 'r' : 'rw';
    }
    return $read_only === 'r';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('jsonapi.settings')
      ->set('read_only', $form_state->getValue('read_only') === 'r')
      ->save();

    parent::submitForm($form, $form_state);
  }

}
