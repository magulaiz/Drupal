<?php

namespace Drupal\workspaces\Entity\Handler;

use Drupal\Core\Entity\EntityInterface;

/**
 * Customizations for block content entities.
 *
 * @internal
 */
class BlockContentWorkspaceHandler extends WorkspaceHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function isEntitySupported(EntityInterface $entity) {
    // Only reusable blocks can be tracked individually. Non-reusable or
    // inline blocks are tracked as part of the entity they are a composite
    // of.
    /** @var \Drupal\block_content\BlockContentInterface $entity */
    return $entity->isReusable();
  }

}
