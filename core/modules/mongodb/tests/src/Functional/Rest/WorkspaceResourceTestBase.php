<?php

declare(strict_types=1);

namespace Drupal\Tests\mongodb\Functional\Rest;

use Drupal\mongodb\modules\workspaces\Workspace;
use Drupal\Tests\workspaces\Functional\Rest\WorkspaceResourceTestBase as CoreWorkspaceResourceTestBase;

/**
 * Base class for workspace EntityResource tests.
 */
abstract class WorkspaceResourceTestBase extends CoreWorkspaceResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['mongodb', 'workspaces'];

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    $workspace = Workspace::create([
      'id' => 'layla',
      'label' => 'Layla',
    ]);
    $workspace->save();
    return $workspace;
  }

}
