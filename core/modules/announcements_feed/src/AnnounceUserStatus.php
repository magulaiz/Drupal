<?php

namespace Drupal\announcements_feed;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\UserData;

/**
 * Service to manage announcements for the user.
 *
 * @internal
 */
class AnnounceUserStatus {

  /**
   * Construct an AnnounceUserStatus object.
   *
   * @param \Drupal\announcements_feed\AnnounceFetcher $fetcher
   *   Fetch the announcements from the external feed.
   * @param \Drupal\user\UserData $userData
   *   Store announcements for the user.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   Current user object.
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cacheTagsInvalidator
   *   Cache invalidator service.
   */
  public function __construct(
    protected AnnounceFetcher $fetcher,
    protected UserData $userData,
    protected AccountProxy $currentUser,
    protected CacheTagsInvalidator $cacheTagsInvalidator
  ) {
  }

  /**
   * Fetch new announcements for the logged in user.
   *
   * Return an array of ids of new announcements for the user.
   *
   * @return string[]
   *   Ids of new announcements for the user, empty array if there are no unread
   *   announcements for the user.
   */
  public function getNewAnnouncementsIds(): array {
    // Get the announcements viewed by the user.
    $old_announcement_ids = $this->getSeenAnnouncementIds();
    // Fetch the announcements from the feed.
    $announcement_ids = $this->fetcher->fetchIds();
    $new_announcement_ids = array_diff($announcement_ids, $old_announcement_ids);
    // Invalidate cache if there are new announcements for the user.
    if (!empty($new_announcement_ids)) {
      $this->cacheTagsInvalidator->invalidateTags(['announcements_feed:feed:' . $this->currentUser->id()]);
    }
    return $new_announcement_ids;
  }

  /**
   * Get announcements for the current logged in user.
   *
   * Return an array of announcements with an additional attribute new for the
   * new items in the list.
   *
   * @return \Drupal\announcements_feed\Announcement[]
   *   Announcements for the current logged in user.
   */
  public function getAllAnnouncements(): array {
    // Fetch announcements from the feed.
    $announcements = $this->fetcher->fetch();
    // Get the new/unread items for the user.
    $new_announcements = $this->getNewAnnouncementsIds();
    foreach ($announcements as &$announcement) {
      // Add an attribute 'new' to the announcements to identify new items.
      $announcement->new = in_array($announcement->id, $new_announcements, TRUE);
    }
    if (!empty($new_announcements)) {
      // Store the ids of announcements for the user to mark it as read items.
      $this->setSeenAnnouncementIds($new_announcements);
    }
    return $announcements;
  }

  /**
   * Get all announcements read by the current user.
   *
   * @return string[]
   *   All announcement ids stored against current user's id, empty if no
   *   announcement data stored for the user.
   */
  protected function getSeenAnnouncementIds(): array {
    $user_announcements = $this->userData->get(
      'announcements_feed',
      $this->currentUser->id(),
      'announcements'
    );
    return $user_announcements ?? [];
  }

  /**
   * Set the read status of announcements for the current user.
   *
   * @parm string[] $announcements
   *  IDs of new announcements to store against the current user.
   */
  protected function setSeenAnnouncementIds(array $new_announcements): void {
    $this->userData->set(
      'announcements_feed',
      $this->currentUser->id(),
      'announcements',
      array_unique(array_merge($this->getSeenAnnouncementIds(), $new_announcements))
    );
  }

}
