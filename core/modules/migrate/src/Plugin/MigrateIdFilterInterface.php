<?php

namespace Drupal\migrate\Plugin;

/**
 * Interface for plugins that support filtering by a set of IDs.
 */
interface MigrateIdFilterInterface {

  /**
   * Sets the list of IDs to filter by.
   *
   * @param array $idList
   *   The list of IDs to filter by.
   */
  public function setIdList(array $idList);

  /**
   * Returns a list of IDs to filter by.
   *
   * @return array
   *   The list of IDs to filter by.
   */
  public function getIdList();

}
