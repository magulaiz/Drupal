<?php

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface for migrate process condition plugins.
 *
 * @ingroup migration
 */
interface MigrateProcessConditionPluginInterface extends PluginInspectionInterface {

  /**
   * Evaluate the condition.
   *
   * @param mixed $source
   *   Source values passed from process plugin.
   *
   * @return bool
   *   TRUE if the condition evaluates as TRUE.
   */
  public function evaluate($source);

}
