<?php

namespace Drupal\system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure System settings for this site.
 */
class MenuLinksetSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_linkset_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.linkset'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['linkset']['enable_endpoint'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the menu linkset endpoint'),
      '#description' => $this->t('See the <a href="@docs-link">decoupled menus documentation</a> for more information.', [
          '@docs-link' => 'https://www.drupal.org/docs/develop/decoupled-drupal/decoupled-menus',
    ]),
      '#default_value' => $this->config('system.linkset')->get('enable_endpoint'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('system.linkset')
      ->set('enable_endpoint', $form_state->getValue('enable_endpoint'))
      ->save();
    \Drupal::service('router.builder')->setRebuildNeeded();
    parent::submitForm($form, $form_state);
  }

}
