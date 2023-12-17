<?php

namespace Drupal\contact\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

class ContactFormCallbacks {

  /**
   * Submit callback for the user profile form to save the contact page setting.
   */
  #[TrustedCallback]
  public static function userProfileFormSubmit(array $form, FormStateInterface $form_state) {
    $account = $form_state->getFormObject()->getEntity();
    if ($account->id() && $form_state->hasValue('contact')) {
      \Drupal::service('user.data')->set('contact', $account->id(), 'enabled', (int) $form_state->getValue('contact'));
    }
  }

  /**
   * Form submission handler for user_admin_settings().
   *
   * @see contact_form_user_admin_settings_alter()
   */
  #[TrustedCallback]
  public static function userAdminSettingsSubmit($form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('contact.settings')
      ->set('user_default_enabled', $form_state->getValue('contact_default_status'))
      ->save();
  }

}
