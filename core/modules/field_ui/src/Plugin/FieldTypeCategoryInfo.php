<?php

namespace Drupal\field_ui\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 *
 */
class FieldTypeCategoryInfo extends PluginBase implements FieldTypeCategoryInfoInterface {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->pluginDefinition['weight'];
  }

}
