<?php

namespace Drupal\Core\Serialization;

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Yaml as ComponentYaml;

/**
 * Provides a YAML serialization implementation.
 *
 * Allow settings to override the YAML implementation resolution.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use
 *   \Drupal\Component\Serialization\Yaml instead.
 *
 * @see https://www.drupal.org/node/111111
 */
class Yaml extends ComponentYaml {

  /**
   * {@inheritdoc}
   */
  protected static function getSerializer() {
    // Allow settings.php to override the YAML serializer.
    if (!isset(static::$serializer) &&
      $class = Settings::get('yaml_parser_class')) {

      static::$serializer = $class;
    }
    return parent::getSerializer();
  }

}
