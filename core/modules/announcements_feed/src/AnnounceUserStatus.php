<?php

namespace Drupal\announcements_feed;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\UserData;

/**
 * Service to manage announcements for the user.
 */
class AnnounceUserStatus {

  /**
   * The UserData service.
   */
  protected UserData $userData;

  /**
   * Current user.
   */
  protected AccountInterface $currentUser;

  /**
   * AnnounceFetcher service.
   */
  protected AnnounceFetcher $fetcher;

  /**
   * Construct an AnnounceUserStatus object.
   *
   * @param \Drupal\announcements_feed\AnnounceFetcher $fetcher
   *   Fetch the announcements from the external feed.
   * @param \Drupal\user\UserData $user_data
   *   Store announcements for the user.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user object.
   */
  public function __construct(AnnounceFetcher $fetcher, UserData $user_data, AccountProxy $current_user) {
    $this->fetcher = $fetcher;
    $this->userData = $user_data;
    $this->currentUser = $current_user;
  }

  /**
   * Fetch new announcements for the logged in user.
   *
   * Return an array of ids of new announcements for the user.
   *
   * @return array
   *   Ids of new announcements for the user, empty array if there are no unread
   *   announcements for the user.
   */
  public function getNewAnnouncements(): array {
    // Get the announcements viewed by the user.
    $old_announcement_ids = $this->getAnnouncementStatus();
    // Fetch the announcements from the feed.
    $announcement_ids = $this->fetcher->fetchIds();
    $new_announcement_ids = array_diff($announcement_ids, $old_announcement_ids);
    // Invalidate cache if there are new announcements for the user.
    if (!empty($new_announcement_ids)) {
      Cache::invalidateTags(['announcements_feed:feed:' . $this->currentUser->id()]);
    }
    return $new_announcement_ids;
  }

  /**
   * Get announcements for the current logged in user.
   *
   * Return an array of announcements with a additional attribute new for the
   * new items in the list.
   *
   * @return array
   *   Announcements for the current logged in user.
   */
  public function getAllAnnouncements(): array {
    // Fetch announcements from the feed.
    $announcements = $this->fetcher->fetch();
    // Get the new/unread items for the user.
    $new_announcements = $this->getNewAnnouncements();
    foreach ($announcements as &$announcement) {
      // Add an attribute 'new' to the announcements to identify new items.
      $announcement['new'] = in_array($announcement['id'], $new_announcements)
        ? $announcement['id'] : '';
    }
    if (!empty($new_announcements)) {
      // Store the ids of announcements for the user to mark it as read items.
      $this->setAnnouncementStatus($this->fetcher->fetchIds());
    }
    return $announcements;

  }

  /**
   * Get all announcements read by the current user.
   *
   * @return array
   *   All announcement ids stored against current user's id, empty if no
   *   announcement data stored for the user.
   */
  protected function getAnnouncementStatus(): array {
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
   * @parm array $announcements
   *  IDs of announcements to store against the current user.
   */
  public function setAnnouncementStatus(array $announcements): void {
    $this->userData->set(
      'announcements_feed',
      $this->currentUser->id(),
      'announcements',
      $announcements
    );
  }

}
