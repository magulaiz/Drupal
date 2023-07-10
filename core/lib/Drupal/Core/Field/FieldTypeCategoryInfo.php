<?php

namespace Drupal\Core\Field;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Default object used for field_type_category_info plugins.
 *
 * @see \Drupal\Core\Field\FieldTypeCategoryInfoManager
 */
class FieldTypeCategoryInfo extends PluginBase implements FieldTypeCategoryInfoInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel(): TranslatableMarkup {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): TranslatableMarkup {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return $this->pluginDefinition['weight'];
  }

}
