<?php

namespace Drupal\user\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Blocks a user.
 */
#[Action(
  id: 'user_block_user_action',
  label: new TranslatableMarkup('Block the selected users'),
  type: 'user'
)]
class BlockUser extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity): void {
    // Skip blocking user if they are already blocked.
    /** @var \Drupal\user\UserInterface $entity */
    if ($entity->isActive()) {
      // For efficiency manually save the original account before applying any
      // changes.
      $entity->original = clone $entity;
      $entity->block();
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
