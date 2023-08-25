<?php

namespace Drupal\file\Upload;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An upload result callback that displays messages.
 */
class MessengerFileUploadedSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new MessagingUploadResultCallback object.
   */
  public function __construct(
    protected MessengerInterface $messenger,
  ) {}

  /**
   * Conditionally displays a message when a file is uploaded.
   */
  public function onFileUploaded(FileUploadedEvent $event): void {
    $result = $event->result;
    if ($result->isRenamed()) {
      if ($result->isSecurityRename()) {
        $this->messenger->addStatus($this->t('For security reasons, your upload has been renamed to %filename.', [
          '%filename' => $result->getFile()->getFilename(),
        ]));
      }
      else {
        $this->messenger->addStatus($this->t('Your upload has been renamed to %filename.', [
          '%filename' => $result->getFile()->getFilename(),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[FileUploadedEvent::class][] = ['onFileUploaded'];
    return $events;
  }

}
