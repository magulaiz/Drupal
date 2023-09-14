<?php

namespace Drupal\workspaces;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines a factory class for workspace operations.
 *
 * @see \Drupal\workspaces\WorkspaceOperationInterface
 * @see \Drupal\workspaces\WorkspacePublisherInterface
 *
 * @internal
 */
class WorkspaceOperationFactory {

  /**
   * Constructs a new WorkspacePublisher.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspaceManager
   *   The workspace manager service.
   * @param \Drupal\workspaces\WorkspaceAssociationInterface $workspaceAssociation
   *   The workspace association service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tags invalidator service.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Psr\Log\LoggerInterface|null $logger
   *   The logger.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager, protected Connection $database, protected WorkspaceManagerInterface $workspaceManager, protected WorkspaceAssociationInterface $workspaceAssociation, protected CacheTagsInvalidatorInterface $cacheTagsInvalidator, protected EventDispatcherInterface $eventDispatcher = NULL, protected ?LoggerInterface $logger = NULL) {
    if (!$eventDispatcher) {
      @trigger_error('The event dispatcher service should be passed to WorkspaceOperationFactory::__construct() since 10.1.0. This will be required in Drupal 11.0.0.', E_USER_DEPRECATED);
      $eventDispatcher = \Drupal::service('eventDispatcher');
    }
    $this->eventDispatcher = $eventDispatcher;
    if ($this->logger === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $logger argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/2932520', E_USER_DEPRECATED);
      $this->logger = \Drupal::service('logger.channel.workspaces');
    }
  }

  /**
   * Gets the workspace publisher.
   *
   * @param \Drupal\workspaces\WorkspaceInterface $source
   *   A workspace entity.
   *
   * @return \Drupal\workspaces\WorkspacePublisherInterface
   *   A workspace publisher object.
   */
  public function getPublisher(WorkspaceInterface $source) {
    return new WorkspacePublisher($this->entityTypeManager, $this->database, $this->workspaceManager, $this->workspaceAssociation, $this->eventDispatcher, $source, $this->logger);
  }

  /**
   * Gets the workspace merger.
   *
   * @param \Drupal\workspaces\WorkspaceInterface $source
   *   The source workspace entity.
   * @param \Drupal\workspaces\WorkspaceInterface $target
   *   The target workspace entity.
   *
   * @return \Drupal\workspaces\WorkspaceMergerInterface
   *   A workspace merger object.
   */
  public function getMerger(WorkspaceInterface $source, WorkspaceInterface $target) {
    return new WorkspaceMerger($this->entityTypeManager, $this->database, $this->workspaceAssociation, $this->cacheTagsInvalidator, $source, $target, $this->logger);
  }

}
