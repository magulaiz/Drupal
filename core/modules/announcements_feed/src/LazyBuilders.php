<?php

declare(strict_types=1);

namespace Drupal\announcements_feed;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Defines a class for lazy building render arrays.
 */
final class LazyBuilders {

  /**
   * Constructs a new LazyBuilders.
   *
   * @param \Drupal\announcements_feed\AnnounceUserStatus $userStatus
   *   User status service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(
    protected AnnounceUserStatus $userStatus,
    protected MessengerInterface $messenger,
  ) {
  }

  /**
   * Render announcements.
   *
   * @return array
   *   Render array.
   */
  public function renderAnouncements(): array {
    $announce_icon = 'announce-default';

    // Check for new announcements for the current user.
    try {
      $new_announcements = $this->userStatus->getNewAnnouncements();
    }
    catch (\Exception $e) {
      $this->messenger->addError('An error occurred while parsing the announcements feed, check the logs for more information.');
    }

    if (!empty($new_announcements)) {
      $announce_icon = 'announce-new';
    }

    return [
        '#type' => 'link',
      '#title' => t('Announcements'),
      '#url' => Url::fromRoute('announcements_feed.announcement'),
      '#attributes' => [
        'title' => t('Announcements'),
        'data-drupal-announce-trigger' => '',
        'class' => [
          'toolbar-icon',
          'toolbar-icon-announce',
          'use-ajax',
          'announce-canvas-link',
          $announce_icon,
        ],
        'data-dialog-renderer' => 'off_canvas',
        'data-dialog-type' => 'dialog',
        'data-dialog-options' => Json::encode(
          [
            'announce' => TRUE,
            'width' => '25%',
            'classes' => [
              'ui-dialog' => 'announce-dialog',
              'ui-dialog-titlebar' => 'announce-titlebar',
              'ui-dialog-title' => 'announce-title',
              'ui-dialog-titlebar-close' => 'announce-close',
              'ui-dialog-content' => 'announce-body',
            ],
          ]),
      ],
      '#attached' => [
        'library' => [
          'announcements_feed/drupal.announcements_feed.toolbar',
        ],
      ],
    ];

  }

}
