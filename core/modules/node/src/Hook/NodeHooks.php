<?php

declare(strict_types=1);

namespace Drupal\node\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\node\NodeStorageInterface;
use Drupal\user\UserInterface;

/**
 * Hook implementations for the node module.
 */
class NodeHooks {

  /**
   * The Node Storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * NodeHooks constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Implements hook_user_cancel().
   *
   * Unpublish nodes (current revisions).
   */
  #[Hook('user_cancel')]
  public function userCancelBlockUnpublish($edit, UserInterface $account, $method): void {
    if ($method === 'user_cancel_block_unpublish') {
      $nids = $this->nodeStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition('uid', $account->id())
        ->execute();
      $this->moduleHandler->invoke('node', 'mass_update', [$nids, ['status' => 0], NULL, TRUE]);
    }
  }

  /**
   * Implements hook_user_cancel().
   *
   * Anonymize all of the nodes for this old account.
   */
  #[Hook('user_cancel')]
  public function userCancelReassign($edit, UserInterface $account, $method): void {
    $uid = ($method === 'user_cancel_reassign_user') ? $edit['user_cancel_assign_user'] : 0;
    if (in_array($method, ['user_cancel_reassign', 'user_cancel_reassign_user'])) {
      $vids = $this->nodeStorage->userRevisionIds($account);
      $this->moduleHandler->invoke('node', 'mass_update',
      [$vids, ['uid' => $uid, 'revision_uid' => 0], NULL, TRUE, TRUE]);
    }
  }

}
