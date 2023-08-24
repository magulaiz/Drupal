<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

/**
 * Contains events related to extensions, and handles their dependencies.
 */
class ExtensionEvents {

  /**
   * Event that occurs when the module list is changed.
   */
  public const MODULE_LIST_WAS_UPDATED = 'extension.module_list_updated';

  /**
   * Event that occurs when a hook rebuild was explicitly requested.
   */
  public const HOOKS_REBUILD_REQUESTED = 'extension.hooks_need_rebuild';

}
