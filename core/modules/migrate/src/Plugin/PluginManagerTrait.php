<?php

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Provides functionality for plugin managers.
 */
trait PluginManagerTrait {

  /**
   * Expand derivative migration dependencies.
   *
   * We need to expand any derivative migrations. Derivative migrations are
   * calculated by migration derivers such as D6NodeDeriver. This allows
   * migrations to depend on the base id and then have a dependency on all
   * derivative migrations. For example, d6_comment depends on d6_node but after
   * we've expanded the dependencies it will depend on d6_node:page,
   * d6_node:story and so on, for other derivative migrations.
   *
   * @return array
   *   An array of expanded plugin ids.
   */
  protected function expandPluginIds(array $migration_ids) {
    $plugin_ids = [];
    foreach ($migration_ids as $id) {
      $plugin_ids += preg_grep('/^' . preg_quote($id, '/') . PluginBase::DERIVATIVE_SEPARATOR . '/', array_keys($this->getDefinitions()));
      if ($this->hasDefinition($id)) {
        $plugin_ids[] = $id;
      }
    }
    return $plugin_ids;
  }

}
