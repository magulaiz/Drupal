<?php

declare(strict_types=1);

namespace Drupal\Core\Executable;

/**
 * An interface for executable plugins.
 *
 * @ingroup plugin_api
 */
interface ExecutableInterface {

  /**
   * Executes the plugin.
   */
  public function execute();

}
