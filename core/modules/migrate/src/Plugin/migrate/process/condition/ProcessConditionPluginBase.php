<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface;

/**
 * The base class for all migrate process condition plugins.
 *
 * @ingroup migration
 */
abstract class ProcessConditionPluginBase extends PluginBase implements MigrateProcessConditionPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!empty($plugin_definition['requires'])) {
      foreach ($plugin_definition['requires'] as $key) {
        if (!isset($configuration[$key])) {
          throw new \InvalidArgumentException("The $key configuration is required when using the $plugin_id process condition.");
        }
      }
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
