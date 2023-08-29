<?php

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * An error handler that adds error messages.
 */
class MessageErrorHandler extends BaseErrorHandler {

  /**
   * Constructs a new MessageErrorHandler object.
   */
  public function __construct(
    LoggerInterface $logger,
    RendererInterface $renderer,
    protected MessengerInterface $messenger,
  ) {
    parent::__construct($logger, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  protected function addErrorMessage(MarkupInterface $message): void {
    $this->messenger->addError($message);
  }

}
