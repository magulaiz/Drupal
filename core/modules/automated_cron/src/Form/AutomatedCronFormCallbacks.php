<?php

namespace Drupal\automated_cron\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

class AutomatedCronFormCallbacks {

  /**
   * Form submission handler for system_cron_settings().
   */
  #[TrustedCallback]
  public static function settingsSubmit(array $form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('automated_cron.settings')
      ->set('interval', $form_state->getValue('interval'))
      ->save();
  }

}
