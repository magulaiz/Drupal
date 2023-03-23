<?php

namespace Drupal\announcements_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\announcements_feed\AnnounceUserStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for community announcements.
 */
class AnnounceController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * AnnounceUserStatus service.
   */
  protected AnnounceUserStatus $userStatus;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an AnnounceController object.
   *
   * @param \Drupal\announcements_feed\AnnounceUserStatus $user_status
   *   The AnnounceUserStatus service.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current_user service.
   */
  public function __construct(AnnounceUserStatus $user_status, AccountProxy $current_user) {
    $this->userStatus = $user_status;
    $this->currentUser = $current_user;
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

    $items = ['featured' => [], 'standard' => []];
    foreach ($announcements as $announcement) {
      if ($announcement['_drupalorg']['featured']) {
        $items['featured'][] = [
          'id' => $announcement['id'],
          'title' => $announcement['title'],
          'teaser' => strip_tags($announcement['content_html']),
          'link' => $announcement['url'],
          'new' => $announcement['new'],
        ];
      }
      else {
        $timestamp = DrupalDateTime::createFromFormat(DATE_ATOM, $announcement['date_published']);
        $items['standard'][] = [
          'id' => $announcement['id'],
          'title' => $announcement['title'],
          'teaser' => strip_tags($announcement['content_html']),
          'link' => $announcement['url'],
          'timestamp' => $timestamp->getTimestamp(),
          'new' => $announcement['new'],
        ];
      }
    }

    $build = [
      '#theme' => 'announcements_feed',
      '#featured' => $items['featured'],
      '#standard' => $items['standard'],
      '#count' => count($announcements),
      '#cache' => [
        'contexts' => [
          'user',
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
      // @todo is this correct? even before the fix I think it should have been announcements-admin?
      $build['#theme'] = 'announcements_feed_admin';
      $build['#attached'] = [];
    }

    return $build;
  }

}
