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
   * @param string[] $source_ids
   *   A list of plugin IDs.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   A plugin manager.
   *
   * @return string[]
   *   An expanded array of plugin IDs. Include the original list and add all
   *   derivatives of plugins in the original list.
   */
  protected function addDerivatives(array $source_ids, PluginManagerInterface $manager): array {
    $plugin_ids = [];
    $all_ids = array_keys($manager->getDefinitions());
    foreach ($source_ids as $id) {
      $plugin_ids += preg_grep('/^' . preg_quote($id, '/') . PluginBase::DERIVATIVE_SEPARATOR . '/', $all_ids);
      if ($manager->hasDefinition($id)) {
        $plugin_ids[] = $id;
      }
    }
    return $plugin_ids;
  }

}
