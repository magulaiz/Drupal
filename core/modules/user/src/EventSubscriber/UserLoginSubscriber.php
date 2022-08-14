<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Event\UserLoginEvent;
use Drupal\user\UserTimestampInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Drupal\user\Event\UserLoginEvent event.
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new event subscriber service.
   *
   * @param UserTimestampInterface $userTimestamp
   *   The user timestamp service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDestination
   *   The redirect destination service.
   */
  public function __construct(
    protected UserTimestampInterface $userTimestamp,
    protected ConfigFactoryInterface $configFactory,
    protected MessengerInterface $messenger,
    protected RedirectDestinationInterface $redirectDestination,
  ) {
  }

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
    $account = $event->getAccount();

    // Update the user login timestamp noting user has logged in. This is also
    // used to invalidate one-time login links.
    $this->userTimestamp->setLastLoginTime($account);

    // Reset static cache of default variables in template_preprocess() to
    // reflect the new user.
    drupal_static_reset('template_preprocess');

    // If the user has a NULL time zone, notify them to set a time zone.
    $config = $this->configFactory->get('system.date');
    if (!$account->getTimezone() && $config->get('timezone.user.configurable') && $config->get('timezone.user.warn')) {
      $this->messenger->addStatus($this->t('Configure your <a href=":user-edit">account time zone setting</a>.', [
        ':user-edit' => $account->toUrl('edit-form', [
          'query' => $this->redirectDestination->getAsArray(),
          'fragment' => 'edit-timezone',
        ])->toString(),
      ]));
    }
  }

}
