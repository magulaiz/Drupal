<?php

namespace Drupal\mongodb\modules\workspaces;

use Drupal\workspaces\Entity\Workspace as CoreWorkspace;
use Drupal\workspaces\WorkspacePublishException;

/**
 * Overriding the entity class \Drupal\workspaces\Entity\Workspace.
 */
class Workspace extends CoreWorkspace {

  /**
   * {@inheritdoc}
   */
  public function publish() {
    try {
      return parent::publish();
    }
    catch (\Exception $e) {
      if ($e instanceof WorkspacePublishException) {
        throw $e;
      }
    }
  }

}
