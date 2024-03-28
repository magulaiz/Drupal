<?php

namespace Drupal\config_translation\FormElement;

@trigger_error('The ' . __NAMESPACE__ . '\PluralVariants is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Instead, use \Drupal\config_translation\FormElement\PluralVariantsElement. See https://www.drupal.org/node/3129216', E_USER_DEPRECATED);

use Drupal\Component\Gettext\PoItem;
use Drupal\Core\Config\Config;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Config\LanguageConfigOverride;

/**
 * Defines form elements for plurals in configuration translation.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use
 *   \Drupal\config_translation\FormElement\PluralVariantsElement.
 *
 * @see https://www.drupal.org/node/3129216
 */
class PluralVariants extends PluralVariantsElement {

  /**
   * {@inheritdoc}
   */
  protected function getSourceElement(LanguageInterface $source_language, $source_config) {
    return parent::getSourceElement($source_language, explode(PoItem::DELIMITER, $source_config));
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslationElement(LanguageInterface $translation_language, $source_config, $translation_config) {
    return parent::getTranslationElement($translation_language, $source_config, explode(PoItem::DELIMITER, $translation_config));
  }

  /**
   * {@inheritdoc}
   */
  public function setConfig(Config $base_config, LanguageConfigOverride $config_translation, $config_values, $base_key = NULL) {
    $config_values = implode(PoItem::DELIMITER, $config_values);
    parent::setConfig($base_config, $config_translation, $config_values, $base_key);
  }

}
