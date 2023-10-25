<?php

namespace Drupal\user\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Unblocks a user.
 */
#[Action(
  id: 'user_unblock_user_action',
  label: new TranslatableMarkup('Unblock the selected users'),
  type: 'user'
)]
class UnblockUser extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity): void {
    // Skip unblocking user if they are already unblocked.
    /** @var \Drupal\user\UserInterface $entity */
    if ($entity->isBlocked()) {
      $entity->activate();
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
