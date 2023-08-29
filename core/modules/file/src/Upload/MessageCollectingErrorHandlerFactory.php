<?php

namespace Drupal\file\Upload;

use Drupal\Core\Render\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating message collecting file upload error handlers.
 *
 * We need to create a new instance each time, as the errors are stored in the
 * object.
 */
class MessageCollectingErrorHandlerFactory {

  /**
   * Constructs a new MessageErrorHandler object.
   */
  public function __construct(
    protected LoggerInterface $logger,
    protected RendererInterface $renderer,
  ) {}

  /**
   * Creates a new MessageCollectingErrorHandler object.
   */
  public function create(): MessageCollectingErrorHandler {
    return new MessageCollectingErrorHandler($this->logger, $this->renderer);
  }

}
