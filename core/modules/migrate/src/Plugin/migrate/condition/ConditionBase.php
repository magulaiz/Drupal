<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Plugin\MigrateConditionInterface;

/**
 * The base class for all migrate condition plugins.
 *
 * @ingroup migration
 */
abstract class ConditionBase extends PluginBase implements MigrateConditionInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!empty($plugin_definition['requires'])) {
      foreach ($plugin_definition['requires'] as $key) {
        if (!isset($configuration[$key])) {
          throw new \InvalidArgumentException("The $key configuration is required when using the $plugin_id condition.");
        }
      }
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
