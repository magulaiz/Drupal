<?php

namespace Drupal\content_translation;

/**
 * Interface providing support for content translation bundle settings.
 */
interface BundleTranslationSettingsInterface {

  /**
   * Returns translation settings for the specified bundle.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param string $bundle
   *   The bundle name.
   *
   * @return array
   *   An associative array of values keyed by setting name.
   */
  public function getBundleTranslationSettings(string $entity_type_id, string $bundle);

  /**
   * Sets translation settings for the specified bundle.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param string $bundle
   *   The bundle name.
   * @param array $settings
   *   An associative array of values keyed by setting name.
   */
  public function setBundleTranslationSettings(string $entity_type_id, string $bundle, array $settings);

}
