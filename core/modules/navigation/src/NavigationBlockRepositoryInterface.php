<?php

namespace Drupal\navigation;

/**
 * Navigation block config entity repository interface.
 */
interface NavigationBlockRepositoryInterface {

  /**
   * Content region of the navigation.
   */
  const REGION_CONTENT = '_navigation_content';

  /**
   * Footer region of the navigation.
   */
  const REGION_FOOTER = '_navigation_footer';

  /**
   * Returns an array of regions and their navigation_block entities.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata[] $cacheable_metadata
   *   (optional) List of CacheableMetadata objects, keyed by region. This is
   *   by reference and is used to pass this information back to the caller.
   *
   * @return array
   *   The array is first keyed by region machine name, with the values
   *   containing an array keyed by navigation_block ID, with
   *   navigation_block entities as the values.
   */
  public function getVisibleBlocksPerRegion(array &$cacheable_metadata = []);

  /**
   * Based on a suggested string generates a unique machine name.
   *
   * @param string $suggestion
   *   The suggested navigation_block ID.
   *
   * @return string
   *   Returns the unique name.
   */
  public function getUniqueMachineName(string $suggestion): string;

}
