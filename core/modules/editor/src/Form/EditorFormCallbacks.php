<?php

namespace Drupal\editor\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Implements trusted form callbacks for editor module.
 */
class EditorFormCallbacks {

  /**
   * Implements #validate callback for editor_form_filter_format_form_alter().
   */
  #[TrustedCallback]
  public static function filterAdminFormatValidate($form, FormStateInterface $form_state) {
    $editor_set = $form_state->getValue(['editor', 'editor']) !== "";
    $subform_array_exists = (!empty($form['editor']['settings']['subform']) && is_array($form['editor']['settings']['subform']));
    if ($editor_set && $subform_array_exists && $editor_plugin = $form_state->get('editor_plugin')) {
      $subform_state = SubformState::createForSubform($form['editor']['settings']['subform'], $form, $form_state);
      $editor_plugin->validateConfigurationForm($form['editor']['settings']['subform'], $subform_state);
    }

    // This handler is not applicable when using the 'Configure' button.
    if ($form_state->getTriggeringElement()['#name'] === 'editor_configure') {
      return;
    }

    // When using this form with JavaScript disabled in the browser, the
    // 'Configure' button won't be clicked automatically. So, when the user has
    // selected a text editor and has then clicked 'Save configuration', we
    // should point out that the user must still configure the text editor.
    if ($form_state->getValue(['editor', 'editor']) !== '' && !$form_state->get('editor')) {
      $form_state->setErrorByName('editor][editor', t('You must configure the selected text editor.'));
    }
  }

}
