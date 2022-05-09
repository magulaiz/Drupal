<?php

namespace Drupal\comment\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\user\Event\AccountCancelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentAccountCancelSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new event subscriber instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __constructor(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // Act before AccountCancelSubscriber::onUserAccountCancel()
      // @see \Drupal\user\EventSubscriber\AccountCancelSubscriber::onUserAccountCancel()
      AccountCancelEvent::class => ['onUserAccountCancel', 20],
    ];
  }

  /**
   * Acts on user account cancel event.
   *
   * @param \Drupal\user\Event\AccountCancelEvent $event
   *   The user cancel event.
   */
  public function onUserAccountCancel(AccountCancelEvent $event): void {
    switch ($event->getMethod()) {
      case 'user_cancel_block_unpublish':
        $comments = $this->entityTypeManager->getStorage('comment')->loadByProperties([
          'uid' => $event->getAccount()->id(),
        ]);
        foreach ($comments as $comment) {
          $comment->setUnpublished()->save();
        }
        break;

      case 'user_cancel_reassign':
        /** @var \Drupal\comment\CommentInterface[] $comments */
        $comments = $this->entityTypeManager->getStorage('comment')->loadByProperties([
          'uid' => $event->getAccount()->id(),
        ]);
        foreach ($comments as $comment) {
          $comment
            ->setOwnerId(0)
            ->setAuthorName($this->configFactory->get('user.settings')->get('anonymous'))
            ->save();
        }
    }
  }

}
