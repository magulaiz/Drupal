<?php

namespace Drupal\field_ui\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Default object used for field_type_category_info plugins.
 *
 * @see \Drupal\field_ui\Plugin\FieldTypeCategoryInfoManager
 */
class FieldTypeCategoryInfo extends PluginBase implements FieldTypeCategoryInfoInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t($this->pluginDefinition['label']);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t($this->pluginDefinition['description']);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->pluginDefinition['weight'];
  }

}
