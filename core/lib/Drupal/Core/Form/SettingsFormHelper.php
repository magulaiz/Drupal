<?php

namespace Drupal\Core\Form;

use Drupal\Component\FileSecurity\FileSecurity;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\FileSystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides form helper methods.
 */
class SettingsFormHelper implements ContainerInjectionInterface {

  /**
   * SettingsFormHelper constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   */
  public function __construct(
    protected LoggerInterface $logger,
    protected FileSystemInterface $fileSystem,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('logger.factory')->get('file system'),
      $container->get('file_system')
    );
  }

  /**
   * Checks the existence of the directory specified in $form_element.
   *
   * This is intended to be used as a #after_build callback.
   *
   * @param array $form_element
   *   The form element containing the name of the directory to check.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form element.
   */
  public static function checkDirectory(array $form_element, FormStateInterface $form_state): array {
    return \Drupal::classResolver()->getInstanceFromDefinition(self::class)->doCheckDirectory($form_element, $form_state);
  }

  /**
   * Checks the existence of the directory specified in $form_element.
   *
   * @param array $form_element
   *   The form element containing the name of the directory to check.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form element.
   */
  protected function doCheckDirectory(array $form_element, FormStateInterface $form_state): array {
    $directory = $form_element['#value'];
    if ($directory === '') {
      return $form_element;
    }

    if (!is_dir($directory) && !$this->fileSystem->mkdir($directory, NULL, TRUE)) {
      // If the directory does not exist and cannot be created.
      $form_state->setErrorByName($form_element['#parents'][0], t('The directory %directory does not exist and could not be created.', ['%directory' => $directory]));
      $this->logger->error('The directory %directory does not exist and could not be created.', ['%directory' => $directory]);
    }

    if (is_dir($directory) && !is_writable($directory) && !$this->fileSystem->chmod($directory)) {
      // If the directory is not writable and cannot be made so.
      $form_state->setErrorByName($form_element['#parents'][0], t('The directory %directory exists but is not writable and could not be made writable.', ['%directory' => $directory]));
      $this->logger->error('The directory %directory exists but is not writable and could not be made writable.', ['%directory' => $directory]);
    }
    elseif (is_dir($directory)) {
      if ($form_element['#name'] === 'file_public_path') {
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
