<?php

namespace Drupal\system\Form;

use Drupal\Component\FileSecurity\FileSecurity;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted callbacks for system module.
 */
class SystemFormCallbacks {

  /**
   * Checks the existence of the directory specified in $form_element.
   *
   * This function is called from the system_settings form to check all core
   * file directories (file_public_path, file_private_path,
   * file_temporary_path).
   *
   * @param array $form_element
   *   The form element containing the name of the directory to check.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  #[TrustedCallback]
  public static function checkDirectory(array $form_element, FormStateInterface $form_state) {
    $directory = $form_element['#value'];
    if (strlen($directory) == 0) {
      return $form_element;
    }

    $logger = \Drupal::logger('file system');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    if (!is_dir($directory) && !$file_system->mkdir($directory, NULL, TRUE)) {
      // If the directory does not exist and cannot be created.
      $form_state->setErrorByName($form_element['#parents'][0], t('The directory %directory does not exist and could not be created.', ['%directory' => $directory]));
      $logger->error('The directory %directory does not exist and could not be created.', ['%directory' => $directory]);
    }

    if (is_dir($directory) && !is_writable($directory) && !$file_system->chmod($directory)) {
      // If the directory is not writable and cannot be made so.
      $form_state->setErrorByName($form_element['#parents'][0], t('The directory %directory exists but is not writable and could not be made writable.', ['%directory' => $directory]));
      $logger->error('The directory %directory exists but is not writable and could not be made writable.', ['%directory' => $directory]);
    }
    elseif (is_dir($directory)) {
      if ($form_element['#name'] == 'file_public_path') {
        // Create public .htaccess file.
        FileSecurity::writeHtaccess($directory, FALSE);
      }
      else {
        // Create private .htaccess file.
        FileSecurity::writeHtaccess($directory);
      }
    }

    return $form_element;
  }

}
