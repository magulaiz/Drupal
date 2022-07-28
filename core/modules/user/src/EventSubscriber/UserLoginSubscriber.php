<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Event\UserLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Drupal\user\Event\UserLoginEvent event.
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * The 'user.timestamp' key value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected KeyValueStoreInterface $keyValue;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected RedirectDestinationInterface $redirectDestination;

  /**
   * Constructs a new event subscriber service.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(TimeInterface $time, KeyValueFactoryInterface $key_value_factory, ConfigFactoryInterface $config_factory, MessengerInterface $messenger, RedirectDestinationInterface $redirect_destination) {
    $this->time = $time;
    $this->keyValue = $key_value_factory->get('user.timestamp');
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->redirectDestination = $redirect_destination;
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
    $account->get('login')->resetComputedValue();
    $this->keyValue->set("{$account->id()}:login", $this->time->getRequestTime());

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
