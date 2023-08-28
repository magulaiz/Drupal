<?php

namespace Drupal\file\Upload;

use Drupal\Component\Utility\Environment;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;

/**
 * An error callback that adds errors to the message system.
 */
class MessageErrorHandler implements FileUploadErrorHandlerInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new MessageCollectingErrorHandler object.
   */
  public function __construct(
    protected MessengerInterface $messenger,
    protected LoggerInterface $logger,
    protected RendererInterface $renderer,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function handleError(UploadedFileInterface $uploadedFile, string $destination, \Exception $e): void {
    switch ($e) {
      case $e instanceof FileExistsException:
        $this->messenger->addError($this->t('Destination file "%file" exists', ['%file' => $destination . $uploadedFile->getFilename()]));
        break;

      case $e instanceof InvalidStreamWrapperException:
        $this->messenger->addError($this->t('The file could not be uploaded because the destination "%destination" is invalid.', ['%destination' => $destination]));
        break;

      case $e instanceof IniSizeFileException:
      case $e instanceof FormSizeFileException:
        $this->messenger->addError($this->t('The file %file could not be saved because it exceeds %maxsize, the maximum allowed size for uploads.', [
          '%file' => $uploadedFile->getFilename(),
          '%maxsize' => format_size(Environment::getUploadMaxSize()),
        ]));
        break;

      case $e instanceof PartialFileException:
      case $e instanceof NoFileException:
        $this->messenger->addError($this->t('The file %file could not be saved because the upload did not complete.', [
          '%file' => $uploadedFile->getFilename(),
        ]));
        break;

      case $e instanceof SymfonyFileException:
        $this->messenger->addError($this->t('The file %file could not be saved. An unknown error has occurred.', [
          '%file' => $uploadedFile->getFilename(),
        ]));
        break;

      case $e instanceof FileValidationException:
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
        $this->messenger->addError($this->renderer->renderPlain($message));
        break;

      case $e instanceof FileWriteException:
        $this->messenger->addError($this->t('File upload error. Could not move uploaded file.'));
        $this->logger->notice('Upload error. Could not move uploaded file %file to destination %destination.', [
          '%file' => $uploadedFile->getClientOriginalName(),
          '%destination' => $destination . '/' . $uploadedFile->getClientOriginalName(),
        ]);
        break;

      case $e instanceof FileException:
        $this->messenger->addError($this->t('The file %filename could not be uploaded because the name is invalid.', [
          '%filename' => $uploadedFile->getClientOriginalName(),
        ]));
        break;
    }
  }

}
