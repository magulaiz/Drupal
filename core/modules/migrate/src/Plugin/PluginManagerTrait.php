<?php

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides functionality for plugin managers.
 */
trait PluginManagerTrait {

  /**
   * Add derivatives to a list of plugin IDs.
   *
   * @param array $migration_ids
   *   A list of plugin IDs.
   * @param \Drupal\Component\Plugin\PluginManagerInterface|null $manager
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
    $all_ids = array_keys($manager->getDefinitions()));
    foreach ($migration_ids as $id) {
      $plugin_ids += preg_grep('/^' . preg_quote($id, '/') . PluginBase::DERIVATIVE_SEPARATOR . '/', $all_ids);
      if ($manager->hasDefinition($id)) {
        $plugin_ids[] = $id;
      }
    }
    return $plugin_ids;
  }

}
