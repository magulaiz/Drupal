<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserTimestampInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Updates the current user's last access time.
 */
class UserRequestSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new UserRequestSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entityTypeManager
   *   (deprecated) The entity type manager service. The $entityTypeManager
   *   parameter is deprecated in drupal:10.1.0 and is removed from
   *   drupal:11.0.0.
   * @param \Drupal\user\UserTimestampInterface|null $userTimestamp
   *   The user timestamp service.
   *
   * @see https://www.drupal.org/node/3300476
   */
  public function __construct(
    protected AccountInterface $account,
    protected ?EntityTypeManagerInterface $entityTypeManager,
    protected ?UserTimestampInterface $userTimestamp = NULL,
  ) {
    if ($this->entityTypeManager) {
      @trigger_error('The $entityTypeManager argument is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3300476', E_USER_DEPRECATED);
    }
    if (!$this->userTimestamp) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $userTimestamp argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3300476', E_USER_DEPRECATED);
      $this->userTimestamp = \Drupal::service('user.timestamp');
    }
  }

  /**
   * Updates the current user's last access time.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event to process.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    $this->userTimestamp->setLastAccessTime($this->account);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Should go before other subscribers start to write their caches. Notably
    // before \Drupal\Core\EventSubscriber\KernelDestructionSubscriber to
    // prevent instantiation of destructed services.
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 300];
    return $events;
  }

}
