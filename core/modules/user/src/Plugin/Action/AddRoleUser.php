<?php

namespace Drupal\user\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\EntityInterface;

/**
 * Adds a role to a user.
 */
#[Action(
  id: 'user_add_role_action',
  label: new TranslatableMarkup('Add a role to the selected users'),
  type: 'user'
)]
class AddRoleUser extends ChangeUserRoleBase {

  /**
   * {@inheritdoc}
   */
  public function execute(EntityInterface $entity): void {
    /** @var \Drupal\user\UserInterface $entity */
    $rid = $this->configuration['rid'];
    // Skip adding the role to the user if they already have it.
    if (!$entity->hasRole($rid)) {
      // For efficiency manually save the original account before applying
      // any changes.
      $entity->original = clone $entity;
      $entity->addRole($rid);
      $entity->save();
    }
  }

}
