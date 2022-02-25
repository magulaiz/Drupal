<?php

namespace Drupal\workspaces\EventSubscriber;

use Drupal\pathauto\Event\PathautoEvents;
use Drupal\pathauto\Event\PathautoSkipEvent;
use Drupal\workspaces\WorkspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to prevent skipping of pathauto alias generation.
 */
class PathautoSkipGenerationSubscriber implements EventSubscriberInterface {

  /**
   * The workspace manager.
   *
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * Constructs a new PathautoSkipGenerationSubscriber instance.
   *
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   */
  public function __construct(WorkspaceManagerInterface $workspace_manager) {
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * Sets the flag for skipping path generation in the live workspace.
   *
   * @param \Drupal\pathauto\Event\PathautoSkipEvent $event
   *   The event dispatched during pathauto path generation.
   */
  public function onPathautoSkipDefaultRevisionGeneration(PathautoSkipEvent $event) {
    // Skip path generation if this is the default revision, and no workspace is
    // active.
    $event->setGenerationSkipped(
      $event->skipPathGeneration() && !$this->workspaceManager->hasActiveWorkspace()
    );
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    $events[PathautoEvents::PATHAUTO_SKIP_DEFAULT_REVISION_GENERATION][] = 'onPathautoSkipDefaultRevisionGeneration';
    return $events;
  }

}
