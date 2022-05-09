<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Event\AccountCancelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to user cancellation event.
 */
class AccountCancelSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The channel logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The channel logger factory service.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('user');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      AccountCancelEvent::class => 'onUserAccountCancel',
    ];
  }

  /**
   * Acts on user account cancel event.
   *
   * @param \Drupal\user\Event\AccountCancelEvent $event
   *   The user cancel event.
   */
  public function onUserAccountCancel(AccountCancelEvent $event): void {
    $account = $event->getAccount();
    $context = $event->getContext();
    switch ($event->getMethod()) {
      case 'user_cancel_block':
      case 'user_cancel_block_unpublish':
      default:
        // Send account blocked notification if option was checked.
        if (!empty($context['user_cancel_notify'])) {
          _user_mail_notify('status_blocked', $account);
        }
        $account->block();
        $account->save();
        $this->messenger->addStatus($this->t('Account %name has been disabled.', [
          '%name' => $account->getDisplayName(),
        ]));
        $this->logger->notice('Blocked user: %name %email.', [
          '%name' => $account->getAccountName(),
          '%email' => '<' . $account->getEmail() . '>',
        ]);
        break;

      case 'user_cancel_reassign':
      case 'user_cancel_delete':
        // Send account canceled notification if option was checked.
        if (!empty($context['user_cancel_notify'])) {
          _user_mail_notify('status_canceled', $account);
        }
        $account->delete();
        $this->messenger->addStatus($this->t('Account %name has been deleted.', [
          '%name' => $account->getDisplayName(),
        ]));
        $this->logger->notice('Deleted user: %name %email.', [
          '%name' => $account->getAccountName(),
          '%email' => '<' . $account->getEmail() . '>',
        ]);
    }
  }

}
