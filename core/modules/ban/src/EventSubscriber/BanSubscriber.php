<?php

namespace Drupal\ban\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\ban\Event\BanEvent;

class BanSubscriber implements EventSubscriberInterface
{
  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager)
  {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      // Static class constant => method on this class.
      BanEvent::EVENT_NAME => 'checkWhitelist',
    ];
  }

  public function checkWhitelist(BanEvent $event)
  {
    if ($this->isWhitelisted($event->ip)) {
      $event->isBanned = false;
    }
  }

  /**
   * Helper function to check whether an Whitelisted IP configuration entity exists.
   */
  public function isWhitelisted($ip)
  {
    $entity = $this->entityTypeManager->getStorage('ban_whitelisted_ip')->getQuery()
      ->condition('whitelistedIp', $ip)
      ->execute();
    return (bool)$entity;
  }
}
