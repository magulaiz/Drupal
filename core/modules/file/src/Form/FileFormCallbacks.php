<?php

namespace Drupal\file\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted callbacks for file module.
 */
class FileFormCallbacks {

  /**
   * Form submission handler for file system settings form.
   */
  #[TrustedCallback]
  public static function systemSettingsSubmit(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('file.settings')
      ->set('filename_sanitization', $form_state->getValue('filename_sanitization'));
    $config->save();
  }

  /**
   * Form submission handler for upload / remove buttons of managed_file elements.
   *
   * @see \Drupal\file\Element\ManagedFile::processManagedFile()
   */
  #[TrustedCallback]
  public static function managedFileSubmit(array $form, FormStateInterface $form_state) {
    // Determine whether it was the upload or the remove button that was clicked,
    // and set $element to the managed_file element that contains that button.
    $parents = $form_state->getTriggeringElement()['#array_parents'];
    $button_key = array_pop($parents);
    $element = NestedArray::getValue($form, $parents);

    // No action is needed here for the upload button, because all file uploads on
    // the form are processed by \Drupal\file\Element\ManagedFile::valueCallback()
    // regardless of which button was clicked. Action is needed here for the
    // remove button, because we only remove a file in response to its remove
    // button being clicked.
    if ($button_key == 'remove_button') {
      $fids = array_keys($element['#files']);
      // Get files that will be removed.
      if ($element['#multiple']) {
        $remove_fids = [];
        foreach (Element::children($element) as $name) {
          if (str_starts_with($name, 'file_') && $element[$name]['selected']['#value']) {
            $remove_fids[] = (int) substr($name, 5);
          }
        }
        $fids = array_diff($fids, $remove_fids);
      }
      else {
        // If we deal with single upload element remove the file and set
        // element's value to empty array (file could not be removed from
        // element if we don't do that).
        $remove_fids = $fids;
        $fids = [];
      }

      foreach ($remove_fids as $fid) {
        // If it's a temporary file we can safely remove it immediately, otherwise
        // it's up to the implementing module to remove usages of files to have them
        // removed.
        if ($element['#files'][$fid] && $element['#files'][$fid]->isTemporary()) {
          $element['#files'][$fid]->delete();
        }
      }
      // Update both $form_state->getValues() and FormState::$input to reflect
      // that the file has been removed, so that the form is rebuilt correctly.
      // $form_state->getValues() must be updated in case additional submit
      // handlers run, and for form building functions that run during the
      // rebuild, such as when the managed_file element is part of a field widget.
      // FormState::$input must be updated so that
      // \Drupal\file\Element\ManagedFile::valueCallback() has correct information
      // during the rebuild.
      $form_state->setValueForElement($element['fids'], implode(' ', $fids));
      NestedArray::setValue($form_state->getUserInput(), $element['fids']['#parents'], implode(' ', $fids));
    }

    // Set the form to rebuild so that $form is correctly updated in response to
    // processing the file removal. Since this function did not change $form_state
    // if the upload button was clicked, a rebuild isn't necessary in that
    // situation and calling $form_state->disableRedirect() would suffice.
    // However, we choose to always rebuild, to keep the form processing workflow
    // consistent between the two buttons.
    $form_state->setRebuild();
  }

}
