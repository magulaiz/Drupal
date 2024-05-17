<?php

namespace Drupal\node\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Hook;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\user\UserInterface;

class NodeHooks {

  protected NodeStorageInterface $nodeStorage;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    protected ModuleHandlerInterface $moduleHandler
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  #[Hook(hook: 'user_cancel')]
  public function userCancel($edit, UserInterface $account, $method) {
    switch ($method) {
      case 'user_cancel_block_unpublish':
        // Unpublish nodes (current revisions).
        $nids = $this->nodeStorage->getQuery()
          ->accessCheck(FALSE)
          ->condition('uid', $account->id())
          ->execute();
        $this->moduleHandler->loadInclude('node', 'inc', 'node.admin');
        node_mass_update($nids, ['status' => 0], NULL, TRUE);
        break;

      case 'user_cancel_reassign':
        // Anonymize all of the nodes for this old account.
        $this->moduleHandler->loadInclude('node', 'inc', 'node.admin');
        $vids = $this->nodeStorage->userRevisionIds($account);
        node_mass_update($vids, [
          'uid' => 0,
          'revision_uid' => 0,
        ], NULL, TRUE, TRUE);
        break;
    }
  }

}
