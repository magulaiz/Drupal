<?php

namespace Drupal\media_library;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for media library UI builder.
 */
interface MediaLibraryUiBuilderInterface {

  /**
   * Get media library dialog options.
   *
   * @return array
   *   The media library dialog options.
   */
  public static function dialogOptions(): array;

  /**
   * Build the media library UI.
   *
   * @param \Drupal\media_library\MediaLibraryState|null $state
   *   (optional) The current state of the media library, derived from the
   *   current request.
   *
   * @return array
   *   The render array for the media library.
   */
  public function buildUi(MediaLibraryState $state = NULL): array;

  /**
   * Check access to the media library.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Drupal\media_library\MediaLibraryState|null $state
   *   (optional) The current state of the media library, derived from the
   *   current request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess(AccountInterface $account, MediaLibraryState $state = NULL): AccessResultInterface;

}
