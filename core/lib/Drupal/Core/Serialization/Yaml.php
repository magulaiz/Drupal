<?php

namespace Drupal\Core\Serialization;

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Yaml as ComponentYaml;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides a YAML serialization implementation.
 *
 * Allow settings to override the YAML implementation resolution.
 */
class Yaml extends ComponentYaml {

  /**
   * Translation Manager.
   */
  protected static TranslationInterface $translation;

  /**
   * {@inheritdoc}
   */
  protected static function getSerializer() {
    // Allow settings.php to override the YAML serializer.
    if (!isset(static::$serializer) &&
      $class = Settings::get('yaml_parser_class')) {

      static::$serializer = $class;

      // Merge any tag callbacks from this proxy to the chosen serializer.
      static::mergeTagCallbacks(static::$serializer);
    }
    return parent::getSerializer();
  }

  /**
   * Retrieves the Translation Manager.
   *
   * @return \Drupal\Core\StringTranslation\TranslationInterface
   *   The Translation Manager.
   */
  protected static function getTranslation(): TranslationInterface {
    if (!isset(static::$translation)) {
      static::$translation = \Drupal::translation();
    }
    return static::$translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultTagCallbacks(): array {
    return [
      '!translate' => static::class . '::applyTranslateCallback',
    ];
  }

  /**
   * Callback for applying the !translate tag.
   *
   * @param string|string[] $value
   *   The tag value.
   * @param string $tag
   *   The tag name.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   A new TranslatableMarkup object.
   */
  public static function applyTranslateCallback(array|string $value, string $tag): TranslatableMarkup {
    return static::getTranslation()->translate(...(array) $value);
  }

}
