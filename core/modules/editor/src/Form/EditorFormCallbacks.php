<?php

namespace Drupal\editor\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Security\Attribute\TrustedCallback;
use Drupal\editor\Entity\Editor;

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

  /**
   * Button submit handler for filter_format_form()'s 'editor_configure' button.
   */
  public static function filterAdminFormatEditorConfigure($form, FormStateInterface $form_state) {
    $editor = $form_state->get('editor');
    $editor_value = $form_state->getValue(['editor', 'editor']);
    if ($editor_value !== NULL) {
      if ($editor_value === '') {
        $form_state->set('editor', FALSE);
        $form_state->set('editor_plugin', NULL);
      }
      elseif (empty($editor) || $editor_value !== $editor->getEditor()) {
        $format = $form_state->getFormObject()->getEntity();
        $editor = Editor::create([
          'format' => $format->isNew() ? NULL : $format->id(),
          'editor' => $editor_value,
        ]);
        $form_state->set('editor', $editor);
      }
    }
    $form_state->setRebuild();
  }

  /**
   * Additional submit handler for filter_format_form().
   */
  #[TrustedCallback]
  public static function filterAdminFormatSubmit(array $form, FormStateInterface $form_state) {
    // Delete the existing editor if disabling or switching between editors.
    $format = $form_state->getFormObject()->getEntity();
    $format_id = $format->isNew() ? NULL : $format->id();
    $original_editor = editor_load($format_id);
    if ($original_editor && $original_editor->getEditor() != $form_state->getValue(['editor', 'editor'])) {
      $original_editor->delete();
    }

    $editor_set = $form_state->getValue(['editor', 'editor']) !== "";
    $subform_array_exists = (!empty($form['editor']['settings']['subform']) && is_array($form['editor']['settings']['subform']));
    if (($editor_plugin = $form_state->get('editor_plugin')) && $editor_set && $subform_array_exists) {
      $subform_state = SubformState::createForSubform($form['editor']['settings']['subform'], $form, $form_state);
      $editor_plugin->submitConfigurationForm($form['editor']['settings']['subform'], $subform_state);
    }

    // Create a new editor or update the existing editor.
    if ($editor = $form_state->get('editor')) {
      // Ensure the text format is set: when creating a new text format, this
      // would equal the empty string.
      $editor->set('format', $format_id);
      if ($settings = $form_state->getValue(['editor', 'settings'])) {
        $editor->setSettings($settings);
      }
      // When image uploads are disabled (status = FALSE), the schema for image
      // upload settings does not allow other keys to be present.
      // @see editor.image_upload_settings.*
      // @see editor.image_upload_settings.1
      // @see editor.schema.yml
      $image_upload_settings = $editor->getImageUploadSettings();
      if (!$image_upload_settings['status']) {
        $editor->setImageUploadSettings(['status' => FALSE]);
      }
      $editor->save();
    }
  }

}
