<?php

namespace Drupal\user_events_test\EventSubscriber;

use Drupal\user\Event\UserAuthenticationEvent;
use Drupal\user\Event\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User authentication subscriber.
 */
class UserAuthenticationSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserEvents::USER_LOGIN => 'onUserLogin',
      UserEvents::USER_LOGOUT => 'onUserLogout',
    ];
  }

  /**
   * Event fires when user is login.
   *
   * @param \Drupal\user\Event\UserAuthenticationEvent $event
   *   User authenticated event.
   */
  public function onUserLogin(UserAuthenticationEvent $event) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $event->getUser();
    // Set the value for user login event testing.
    /* @see \Drupal\Tests\user\Kernel\UserAuthenticationEventSubscriberTest::setUp */
    $user->set('test_field', 'subscriber_event_triggered');
  }

  /**
   * Event fires when user is logout.
   *
   * @param \Drupal\user\Event\UserAuthenticationEvent $event
   *   User authenticated event.
   */
  public function onUserLogout(UserAuthenticationEvent $event) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $event->getUser();
    // Set the value for user logout event testing.
    /* @see \Drupal\Tests\user\Kernel\UserAuthenticationEventSubscriberTest::setUp */
    $user->set('test_field', 'subscriber_event_triggered');
  }

}
