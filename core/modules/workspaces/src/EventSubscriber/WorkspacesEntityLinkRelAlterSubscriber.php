<?php

namespace Drupal\workspaces\EventSubscriber;

use Drupal\Core\Entity\Event\EntityEvents;
use Drupal\Core\Entity\Event\EntityLinkRelAlterEvent;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\workspaces\WorkspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber alter link rel of entity links.
 */
class WorkspacesEntityLinkRelAlterSubscriber implements EventSubscriberInterface {

  /**
   * The workspace manager.
   *
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * Constructs a new EntityLinkRelAlterSubscriber instance.
   *
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   */
  public function __construct(WorkspaceManagerInterface $workspace_manager) {
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * Sets the link rel of the default revision to canonical instead of revision.
   *
   * @param \Drupal\Core\Entity\Event\EntityLinkRelAlterEvent $event
   *   The event dispatched in EntityBase::toUrl().
   */
  public function onEntityLinkRelAlter(EntityLinkRelAlterEvent $event) {
    $rel = $event->getRel();
    $entity = $event->getEntity();

    // Links of entities outside a workspace which are pointing to the current
    // revision point to the actual entity. So instead of using the 'revision'
    // link, use the 'canonical' link. Inside a workspace, non default revisions
    // should still point to canonical to ensure url aliases are handled
    // properly inside workspaces too.
    if (($entity->isDefaultRevision() && !$this->workspaceManager->hasActiveWorkspace()) || $this->workspaceManager->hasActiveWorkspace()) {
      $rel = 'canonical';
    }
    $event->setRel($rel);
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    // Increased weight to run after core's EntityLinkRelAlterSubscriber.
    $events[EntityEvents::ENTITY_LINK_REL_ALTER][] = ['onEntityLinkRelAlter', 0];
    return $events;
  }

}
