<?php

namespace Drupal\Core\File\MimeType;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface as LegacyMimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Fallback MIME type guesser that always returns 'application/octet-stream'.
 */
class FallbackMimeTypeGuesser implements MimeTypeGuesserInterface, LegacyMimeTypeGuesserInterface {

  /**
   * {@inheritdoc}
   */
  public function guess($path) {
    return $this->guessMimeType($path);
  }

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
