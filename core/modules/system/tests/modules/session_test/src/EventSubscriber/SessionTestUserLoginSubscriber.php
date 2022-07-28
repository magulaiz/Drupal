<?php

namespace Drupal\session_test\EventSubscriber;

use Drupal\user\Event\UserLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Drupal\user\Event\UserLoginEvent event.
 */
class SessionTestUserLoginSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      UserLoginEvent::class => 'onUserLogin',
    ];
  }

  /**
   * Performs tasks after a successful user login.
   *
   * @param \Drupal\user\Event\UserLoginEvent $event
   *   The user login event.
   */
  public function onUserLogin(UserLoginEvent $event): void {
    if ($event->getAccount()->getAccountName() == 'session_test_user') {
      // Exit so we can verify that the session was regenerated
      // before hook_user_login() was called.
      exit;
    }
    // Add some data in the session for retrieval testing purpose.
    \Drupal::request()->getSession()->set("session_test_key", "foobar");
  }

}
