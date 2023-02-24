<?php

namespace Drupal\block_content\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\block_content\BlockContentTypeInterface;

/**
 * Determines access for block content add pages.
 *
 * @ingroup block_content_access
 */
class BlockContentAddAccessCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks access to the block content add page for the block type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\block_content\BlockContentTypeInterface $block_content_type
   *   (optional) The block type. If not specified, access is allowed if there
   *   exists at least one block type for which the user may create a block.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, BlockContentTypeInterface $block_content_type = NULL) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('block_content');
    // If checking whether a block of a particular type may be created.
    if ($account->hasPermission('administer blocks')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if ($block_content_type) {
      return $access_control_handler->createAccess($block_content_type->id(), $account, [], TRUE);
    }
    // If checking whether a block of any type may be created.
    foreach ($this->entityTypeManager->getStorage('block_content_type')->loadMultiple() as $block_content_type) {
      if (($access = $access_control_handler->createAccess($block_content_type->id(), $account, [], TRUE)) && $access->isAllowed()) {
        return $access;
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
