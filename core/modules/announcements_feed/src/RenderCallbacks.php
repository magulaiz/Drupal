<?php

declare(strict_types=1);

namespace Drupal\announcements_feed;

use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Defines a class for render callbacks.
 *
 * @internal
 */
final class RenderCallbacks {

  /**
   * Render callback.
   */
  #[TrustedCallback]
  public static function removeTabAttributes(array $element): array {
    unset($element['tab']['#attributes']);
    return $element;
  }

}
