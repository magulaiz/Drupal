<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Updates the current user's last access time.
 */
class UserRequestSubscriber implements EventSubscriberInterface {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The 'user.timestamp.access' key value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected KeyValueStoreInterface $keyValue;

  /**
   * The datetime service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Constructs a new UserRequestSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entity_type_manager
   *   (deprecated) The entity type manager service. The $entity_type_manager
   *   parameter is deprecated in drupal:10.1.0 and is removed from
   *   drupal:11.0.0.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value factory service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The datetime service.
   *
   * @see https://www.drupal.org/node/3300476
   */
  public function __construct(AccountInterface $account, ?EntityTypeManagerInterface $entity_type_manager = NULL, KeyValueFactoryInterface $key_value_factory = NULL, TimeInterface $time = NULL) {
    $this->account = $account;
    if ($entity_type_manager) {
      @trigger_error('The $entity_type_manager argument is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3300476', E_USER_DEPRECATED);
    }
    $this->entityTypeManager = $entity_type_manager;
    if (!$key_value_factory) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $key_value_factory argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3300476', E_USER_DEPRECATED);
      $key_value_factory = \Drupal::service('keyvalue');
    }
    $this->keyValue = $key_value_factory->get('user.timestamp.access');
    if (!$time) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $time argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3300476', E_USER_DEPRECATED);
      $time = \Drupal::service('datetime.time');
    }
    $this->time = $time;
  }

  /**
   * Updates the current user's last access time.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event to process.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    if ($this->account->isAuthenticated() && $this->time->getRequestTime() - $this->account->getLastAccessedTime() > Settings::get('session_write_interval', 180)) {
      // Do that no more than once per 180 seconds.
      $this->keyValue->set($this->account->id(), $this->time->getRequestTime());
    }
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
