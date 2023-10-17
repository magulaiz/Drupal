<?php

namespace Drupal\olivero\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted form callbacks for olivero theme.
 */
class OliveroFormCallbacks {

  /**
   * Implements #validate callback for the Olivero system_theme_settings form.
   */
  #[TrustedCallback]
  public static function themeSettingsValidate($form, FormStateInterface $form_state):void {
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $form_state->getValue('base_primary_color'))) {
      $form_state->setErrorByName('base_primary_color', t('Colors must be 7-character string specifying a color hexadecimal format.'));
    }
  }

}
