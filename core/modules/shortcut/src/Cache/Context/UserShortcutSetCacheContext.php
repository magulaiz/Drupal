<?php

declare(strict_types=1);

namespace Drupal\shortcut\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\Context\UserCacheContextBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\shortcut\ShortcutSetInterface;

/**
 * Defines the UserShortcutSetCacheContext, for "per shortcut set" caching.
 */
class UserShortcutSetCacheContext extends UserCacheContextBase implements CacheContextInterface {

  /**
   * Constructs a new UserShortcutSetCacheContext service.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $user, protected EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($user);
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("User's shortcut set");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): string {
    return $this->displayedShortcutSet()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return (new CacheableMetadata())->addCacheableDependency($this->displayedShortcutSet());
  }

  /**
   * Gets the user's shortcut set displayed.
   *
   * @return \Drupal\shortcut\ShortcutSetInterface
   *   The user's shortcut set displayed.
   */
  protected function displayedShortcutSet(): ShortcutSetInterface {
    return $this->entityTypeManager
      ->getStorage('shortcut_set')
      ->getDisplayedToUser($this->user);
  }

}
