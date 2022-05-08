<?php

namespace Drupal\Core\Entity\Menu;

/**
 * Defines an interface for entity link providers.
 */
interface EntityLinksProviderInterface {

  /**
   * Defines derivative menu link plugins for an entity type.
   *
   * @param array $base_plugin_definition
   *   The definition array of the base plugin.
   *
   * @return array
   *   An array of full derivative definitions keyed on derivative id.
   */
  public function getMenuLinks(array $base_plugin_definition): array;

  /**
   * Defines derivative task link plugins for an entity type.
   *
   * @param array $base_plugin_definition
   *   The definition array of the base plugin.
   *
   * @return array
   *   An array of full derivative definitions keyed on derivative id.
   */
  public function getTaskLinks(array $base_plugin_definition): array;

  /**
   * Defines derivative action link plugins for an entity type.
   *
   * @param array $base_plugin_definition
   *   The definition array of the base plugin.
   *
   * @return array
   *   An array of full derivative definitions keyed on derivative id.
   */
  public function getActionLinks(array $base_plugin_definition): array;

}
