<?php

declare(strict_types=1);

namespace Drupal\announcements_feed;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\UserData;

/**
 * Service to manage announcements for the user.
 *
 * @internal
 */
class AnnounceUserStatus {

  /**
   * The configuration settings of this module.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param string $feedUrl
   *   The feed url path.
   */
  public function __construct(
    protected AnnounceFetcher $fetcher,
    protected UserData $userData,
    protected AccountProxy $currentUser,
    protected CacheTagsInvalidator $cacheTagsInvalidator,
    ConfigFactoryInterface $config,
    protected string $feedUrl
  ) {
    $this->config = $config->get('announcements_feed.settings');
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
    return $user_announcements[$this->feedUrl] ?? [];
  }

  /**
   * Set the read status of announcements for the current user.
   *
   * @parm string[] $announcements
   *  IDs of new announcements to store against the current user.
   */
  protected function setSeenAnnouncementIds(array $new_announcements): void {
    $user_announcements = $this->userData->get(
      'announcements_feed',
      $this->currentUser->id(),
      'announcements'
    );

    // Merge the new ones with the previous ones.
    $announcements = array_unique(array_merge($new_announcements, $user_announcements[$this->feedUrl] ?? []));

    // Per-user limit is usually higher than the announcements feed limit just
    // in case the limit actually changes. We don't want already seen messages
    // to show up as new again (ie: if the limit is made bigger), but we don't
    // want the array to grow forever either.
    $announcements = array_slice($announcements, 0, $this->config->get('per_user_limit') ?? 40);

    // Store the announcements per feed URL in case this changes.
    $user_announcements[$this->feedUrl] = $announcements;

    // We no longer need the data from other feeds if the feed changed.
    $user_announcements = array_intersect_key($user_announcements, array_flip([$this->feedUrl]));

    $this->userData->set(
      'announcements_feed',
      $this->currentUser->id(),
      'announcements',
      $user_announcements
    );
  }

}
