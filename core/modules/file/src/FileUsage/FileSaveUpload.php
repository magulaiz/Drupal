<?php

namespace Drupal\file\FileUsage;

use Drupal\Core\File\Exception\FileWriteException;
use Drupal\file\Upload\FileValidationException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\file\Upload\FormUploadedFile;
use Drupal\Core\File\Exception\FileExistsException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Drupal\Component\Utility\Environment;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;

/**
 * Class with old method file_save_upload.
 */
class FileSaveUpload {

  /**
   * Method used to upload files.
   */
  public function file_save_upload($form_field_name, $validators = [], $destination = FALSE, $delta = NULL, $replace = FileSystemInterface::EXISTS_RENAME) {
    static $upload_cache;

    $all_files = \Drupal::request()->files->get('files', []);
    // Make sure there's an upload to process.
    if (empty($all_files[$form_field_name])) {
      return NULL;
    }
    $file_upload = $all_files[$form_field_name];

    // Return cached objects without processing since the file will have
    // already been processed and the paths in $_FILES will be invalid.
    if (isset($upload_cache[$form_field_name])) {
      if (isset($delta)) {
        return $upload_cache[$form_field_name][$delta];
      }
      return $upload_cache[$form_field_name];
    }

    // Prepare uploaded files info. Representation is slightly different
    // for multiple uploads and we fix that here.
    $uploaded_files = $file_upload;
    if (!is_array($file_upload)) {
      $uploaded_files = [$file_upload];
    }

    if ($destination === FALSE || $destination === NULL) {
      $destination = 'temporary://';
    }

    /** @var \Drupal\file\Upload\FileUploadHandler $file_upload_handler */
    $file_upload_handler = \Drupal::service('file.upload_handler');
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $files = [];
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploaded_file */
    foreach ($uploaded_files as $i => $uploaded_file) {
      try {
        $form_uploaded_file = new FormUploadedFile($uploaded_file);
        $result = $file_upload_handler->handleFileUpload($form_uploaded_file, $validators, $destination, $replace);
        $file = $result->getFile();
        // If the filename has been modified, let the user know.
        if ($result->isRenamed()) {
          if ($result->isSecurityRename()) {
            $message = t('For security reasons, your upload has been renamed to %filename.', ['%filename' => $file->getFilename()]);
          }
          else {
            $message = t('Your upload has been renamed to %filename.', ['%filename' => $file->getFilename()]);
          }
          \Drupal::messenger()->addStatus($message);
        }
        $files[$i] = $file;
      }
      catch (FileExistsException $e) {
        \Drupal::messenger()->addError(t('Destination file "%file" exists', ['%file' => $destination . $uploaded_file->getFilename()]));
        $files[$i] = FALSE;
      }
      catch (InvalidStreamWrapperException $e) {
        \Drupal::messenger()->addError(t('The file could not be uploaded because the destination "%destination" is invalid.', ['%destination' => $destination]));
        $files[$i] = FALSE;
      }
      catch (IniSizeFileException | FormSizeFileException $e) {
        \Drupal::messenger()->addError(t('The file %file could not be saved because it exceeds %maxsize, the maximum allowed size for uploads.', [
          '%file' => $uploaded_file->getFilename(),
          '%maxsize' => format_size(Environment::getUploadMaxSize()),
        ]));
        $files[$i] = FALSE;
      }
      catch (PartialFileException | NoFileException $e) {
        \Drupal::messenger()->addError(t('The file %file could not be saved because the upload did not complete.', [
          '%file' => $uploaded_file->getFilename(),
        ]));
        $files[$i] = FALSE;
      }
      catch (FileValidationException $e) {
        \Drupal::messenger()->addError(t('The file %file could not be saved. An unknown error has occurred.', ['%file' => $uploaded_file->getFilename()]));
        $files[$i] = FALSE;
      }
      catch (FileValidationException $e) {
        $message = [
          'error' => [
            '#markup' => t('The specified file %name could not be uploaded.', ['%name' => $e->getFilename()]),
          ],
          'item_list' => [
            '#theme' => 'item_list',
            '#items' => $e->getErrors(),
          ],
        ];
        // @todo Add support for render arrays in
        // \Drupal\Core\Messenger\MessengerInterface::addMessage()?
        // @see https://www.drupal.org/node/2505497.
        \Drupal::messenger()->addError($renderer->renderPlain($message));
        $files[$i] = FALSE;
      }
      catch (FileWriteException $e) {
        \Drupal::messenger()->addError(t('File upload error. Could not move uploaded file.'));
        \Drupal::logger('file')->notice('Upload error. Could not move uploaded file %file to destination %destination.', ['%file' => $uploaded_file->getClientOriginalName(), '%destination' => $destination . '/' . $uploaded_file->getClientOriginalName()]);
        $files[$i] = FALSE;
      }
      catch (FileException $e) {
        \Drupal::messenger()->addError(t('The file %filename could not be uploaded because the name is invalid.', ['%filename' => $uploaded_file->getClientOriginalName()]));
        $files[$i] = FALSE;
      }
    }

    // Add files to the cache.
    $upload_cache[$form_field_name] = $files;

    return isset($delta) ? $files[$delta] : $files;
  }

}
