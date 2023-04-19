<?php

namespace Drupal\announcements_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\announcements_feed\AnnounceUserStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for community announcements.
 */
class AnnounceController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs an AnnounceController object.
   *
   * @param \Drupal\announcements_feed\AnnounceUserStatus $userStatus
   *   The AnnounceUserStatus service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current_user service.
   */
  public function __construct(protected AnnounceUserStatus $userStatus, AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): AnnounceController {
    return new static(
      $container->get('announcements_feed.user_status'),
      $container->get('current_user')
    );
  }

  /**
   * Returns Announcements for the current user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   A build array with announcements for the current user.
   */
  public function getAnnouncements(Request $request): array {
    try {
      $announcements = $this->userStatus->getAllAnnouncements();
    }
    catch (\Exception $e) {
      return [
        '#theme' => 'status_messages',
        '#message_list' => [
          'error' => [
            $this->t('An error occurred while parsing the announcements feed, check the logs for more information.'),
          ],
        ],
        '#status_headings' => [
          'error' => $this->t('Error Message'),
        ],
      ];
    }

    $build = [];
    foreach ($announcements as $announcement) {
      if ($announcement->featured) {
        $build['#featured'][] = $announcement->normalize() + [
          'teaser' => strip_tags($announcement->content_html),
          'link' => $announcement->url,
        ];
        continue;
      }
      $timestamp = DrupalDateTime::createFromFormat(DATE_ATOM, $announcement->date_published);
      $build['#standard'][] = $announcement->normalize() + [
        'teaser' => strip_tags($announcement->content_html),
        'link' => $announcement->url,
        'timestamp' => $timestamp->getTimestamp(),
      ];
    }

    $build += [
      '#theme' => 'announcements_feed',
      '#count' => count($announcements),
      '#cache' => [
        'contexts' => [
          'user',
          'url.query_args:_wrapper_format',
        ],
        'tags' => [
          'announcements_feed:feed:' . $this->currentUser->id(),
        ],
      ],
      '#attached' => [
        'library' => [
          'announcements_feed/drupal.announcements_feed.dialog',
        ],
      ],
    ];
    if ($request->query->get('_wrapper_format') != 'drupal_dialog.off_canvas') {
      $build['#theme'] = 'announcements_feed_admin';
      $build['#attached'] = [];
    }

    return $build;
  }

}
