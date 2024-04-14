<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\standard\Form\StandardProfileFormCallbacks;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function standard_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  $form['#submit'][] = [StandardProfileFormCallbacks::class, 'submitFormSubmitted'];
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use
 *  StandardProfileFormCallbacks::submitFormSubmitted()
 *  instead.
 *
 * @todo Replace with CR url for issue 2966711
 * @see https://www.drupal.org/project/drupal/issues/2966711
 */
function standard_form_install_configure_submit($form, FormStateInterface $form_state) {
  StandardProfileFormCallbacks::installConfigureSubmit($form, $form_state);
}
