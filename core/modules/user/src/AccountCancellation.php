<?php

namespace Drupal\user;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Event\AccountCancelEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Provides user account cancellation functionality.
 */
class AccountCancellation {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface $seesion
   */
  protected $session;

  /**
   * Constructs a new service instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface
   *   The channel logger factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __constructor(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory, ModuleHandlerInterface $module_handler, AccountProxyInterface $current_user, EventDispatcherInterface $event_dispatcher, SessionInterface $session) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('user');
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Cancels a user account.
   *
   * @param int $uid
   *   The user ID of the user account to cancel.
   * @param string $method
   *   The account cancellation method to use.
   * @param array $context
   *   (optional) Context array. Typically, an array of submitted form values as
   *   this service is consumed via form API.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function cancel(int $uid, string $method, array $context = []): void {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->entityTypeManager->getStorage('user');

    if (!$account) {
      $arguments = ['%id' => $uid];
      $this->messenger->addError($this->t('The user account %id does not exist.', $arguments));
      $this->logger->error('Attempted to cancel non-existing user account: %id.', $arguments);
      return;
    }

    // Initialize batch (to set title).
    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Cancelling account'));
    batch_set($batch_builder->toArray());

    // When the 'user_cancel_delete' method is used, user_delete() is called,
    // which invokes hook_ENTITY_TYPE_predelete() and hook_ENTITY_TYPE_delete()
    // for the user entity. Modules should use those hooks to respond to the
    // account deletion.
    if ($method !== 'user_cancel_delete') {
      // Allow modules to add further sets to this batch.
      $this->moduleHandler->invokeAll('user_cancel', [$context, $account, $method]);
    }

    // Finish the batch and actually cancel the account.
    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Cancelling user account'))
      ->addOperation([$this, 'doCancelAccount'], [$account, $method, $context]);

    // After cancelling account, ensure that user is logged out.
    if ($account->id() == \Drupal::currentUser()->id()) {
      // Batch API stores data in the session, so use the finished operation to
      // manipulate the current user's session id.
      $batch_builder->setFinishCallback([$this, 'regenerateSession']);
    }

    batch_set($batch_builder->toArray());

    // Batch processing is either handled via Form API or has to be invoked
    // manually.
  }

  /**
   * Provides a batch API operation that cancels the account.
   *
   * Last step for cancelling a user account. Since batch and session API
   * require a valid user account, the actual cancellation of a user account
   * needs to happen last.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to be cancelled.
   * @param string $method
   *   The account cancellation method to use.
   * @param array $context
   *   Context array. Typically, an array of submitted form values as this
   *   service is consumed via form API.
   */
  public function doCancelAccount(UserInterface $account, string $method, array $context): void {
    $account_cancel_event = new AccountCancelEvent($account, $method, $context);
    $this->eventDispatcher->dispatch($account_cancel_event);

    // After cancelling account, ensure that user is logged out. We can't
    // destroy their session though, as we might have information in it, and we
    // can't regenerate it because batch API uses the session ID, we will
    // regenerate it in ::regenerateSession().
    if ($account->id() === $this->currentUser->id()) {
      $this->currentUser->setAccount(new AnonymousUserSession());
    }
  }

  /**
   * Provides a finished batch processing callback for cancelling user account.
   */
  public function regenerateSession(): void {
    // Regenerate the user's session instead of calling session_destroy() as we
    // want to preserve any messages that might have been set.
    $this->session->migrate();
  }

}
