<?php

namespace Drupal\Core\Entity;

/**
 * Provides an interface for an entity type bundle info.
 */
interface EntityTypeBundleInfoInterface {

  /**
   * Get the bundle info of all entity types.
   *
   * @return array[]
   *   An array of all bundle information, whose keys are entity type IDs and
   *   values are (second-level) arrays whose keys are bundle names, and
   *   values are (third-level) arrays with the same structure as the return
   *   value of static::getBundleInfo().
   *
   * @see static::getBundleInfo()
   */
  public function getAllBundleInfo();

  /**
   * Gets the bundle info of an entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return array[]
   *   An array of bundle information whose keys are bundle names and values
   *   are (second-level) arrays with the following keys:
   *   - label: The human-readable name of the bundle.
   *   - uri_callback: The same as the 'uri_callback' key defined for the entity
   *     type in its definition, but for the bundle only. When determining the
   *     URI of an entity, if a 'uri_callback' is defined for both the entity
   *     type and the bundle, the one for the bundle is used.
   *   - translatable: (optional) TRUE if the bundle has translation support,
   *     FALSE (default) if not.
   */
  public function getBundleInfo($entity_type_id);

  /**
   * Clears static and persistent bundles.
   */
  public function clearCachedBundles();

}
