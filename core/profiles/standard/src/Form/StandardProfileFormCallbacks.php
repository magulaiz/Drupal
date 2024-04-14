<?php

namespace Drupal\standard\Form;

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted callbacks for the standard profile.
 */
class StandardProfileFormCallbacks {

  /**
   * Implements #submission handler.
   *
   * Syncs the contact.form.feedback recipient.
   *
   * @see standard_form_install_configure_form_alter()
   */
  #[TrustedCallback]
  public static function installConfigureSubmit(array $form, FormStateInterface $form_state) {
    $site_mail = $form_state->getValue('site_mail');
    ContactForm::load('feedback')->setRecipients([$site_mail])->trustData()->save();
  }

}
