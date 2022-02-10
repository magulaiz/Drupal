<?php

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginManagerInterface;

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
   * @param array $migration_ids
   *   A list of plugin IDs.
   * @param PluginManagerInterface $manager
   *   (optional) A plugin manager. Defaults to $this, so it should be supplied
   *   explicitly except in a class that implements PluginManagerInterface.
   *
   * @return array
   *   An expanded array of plugin ids. Include the original list and add all
   *   derivatives of plugins in the original list.
   */
  protected function expandPluginIds(array $migration_ids, ?PluginManagerInterface $manager = NULL) {
    if ($manager === NULL) {
      $manager = $this;
    }
    $plugin_ids = [];
    foreach ($migration_ids as $id) {
      $plugin_ids += preg_grep('/^' . preg_quote($id, '/') . PluginBase::DERIVATIVE_SEPARATOR . '/', array_keys($manager->getDefinitions()));
      if ($manager->hasDefinition($id)) {
        $plugin_ids[] = $id;
      }
    }
    return $plugin_ids;
  }

}
