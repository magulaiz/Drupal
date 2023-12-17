<?php

namespace Drupal\language_elements_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\language\Form\LanguageFormCallbacks;

/**
 * A form containing a language configuration element.
 *
 * @internal
 */
class LanguageConfigurationElement extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'language_elements_configuration_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $conf = ContentLanguageSettings::loadByEntityTypeBundle('entity_test', 'some_bundle');

    $form['lang_configuration'] = [
      '#type' => 'language_configuration',
      '#entity_information' => [
        'entity_type' => 'entity_test',
        'bundle' => 'some_bundle',
      ],
      '#default_value' => $conf,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];
    $form['#submit'][] = [LanguageFormCallbacks::class, 'configurationElementSubmit'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
