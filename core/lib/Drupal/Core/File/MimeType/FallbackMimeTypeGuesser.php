<?php

namespace Drupal\Core\File\MimeType;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Fallback MIME type guesser that always returns 'application/octet-stream'.
 */
class FallbackMimeTypeGuesser implements MimeTypeGuesserInterface {

  /**
   * {@inheritdoc}
   */
  public function guessMimeType($path): ?string {
    return 'application/octet-stream';
  }

  /**
   * {@inheritdoc}
   */
  public function isGuesserSupported(): bool {
    return TRUE;
  }

}
