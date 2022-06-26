<?php

namespace Drupal\user;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Event\AccountCancelEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Provides user account cancellation functionality.
 *
 * Third-party, having something to say on user cancellation, should subscribe
 * to \Drupal\user\Event\AccountCancelEvent event. The 'user' module provides
 * its own subscriber (\Drupal\user\EventSubscriber\AccountCancelSubscriber)
 * that actually performs the cancellation. A subscriber aiming to implement its
 * own user account cancellation logic and avoid default core behavior should
 * set a higher priority and bypass downstream subscribers by stopping the event
 * propagation.
 *
 * @see \Drupal\user\Event\AccountCancelEvent
 * @see \Drupal\user\EventSubscriber\AccountCancelSubscriber
 */
class AccountCancellation {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The channel logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructs a new service instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The channel logger factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory, ModuleHandlerInterface $module_handler, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('user');
    $this->moduleHandler = $module_handler;
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
    $account = $this->entityTypeManager->getStorage('user')->load($uid);

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

    // When the 'user_cancel_delete' method is used, the user entity is deleted,
    // which invokes hook_ENTITY_TYPE_predelete() and hook_ENTITY_TYPE_delete().
    // Modules should use those hooks to respond to the account deletion.
    if ($method !== 'user_cancel_delete') {
      // Allow modules to add further sets to this batch.
      $description = 'The hook is deprecated in drupal:9.5.0 and is removed from drupal:10.0.0. In order to act on user account cancellation provide an event subscriber that listens to the \Drupal\user\Event\AccountCancelEvent event. The event subscriber can be defined with a priority higher than the core subscribers in order to cancel them by using AccountCancelEvent::stopPropagation(). See https://www.drupal.org/node/3279455';
      $this->moduleHandler->invokeAllDeprecated($description, 'user_cancel', [$context, $account, $method]);
    }

    // Allow third-party to add further sets to this batch.
    $account_cancel_event = new AccountCancelEvent($account, $method, $context);
    $this->eventDispatcher->dispatch($account_cancel_event);

    // After cancelling account, ensure that user is logged out.
    if ($account->id() == \Drupal::currentUser()->id()) {
      // Batch API stores data in the session, so use the finished operation to
      // manipulate the current user's session ID.
      $batch_builder = (new BatchBuilder())->setFinishCallback(static::class . '::regenerateSession');
      batch_set($batch_builder->toArray());
    }

    // Batch processing is either handled via Form API or has to be invoked
    // manually.
  }

  /**
   * Provides a finished batch processing callback for cancelling user account.
   *
   * Note that this method is declared static to avoid serialization of a huge
   * object by the batch API.
   */
  public static function regenerateSession(): void {
    // Regenerate the user's session instead of calling session_destroy() as we
    // want to preserve any messages that might have been set.
    \Drupal::service('session')->migrate();
  }

}
