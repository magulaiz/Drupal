<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

/**
 * Interface for a list of active modules.
 */
interface ActiveModuleListInterface {

  /**
   * List of installed modules.
   *
   * @return array<string, \Drupal\Core\Extension\Extension>
   */
  public function getModules(): array;

  /**
   * Gets a module by name.
   *
   * @param string $name
   *   Module name.
   *
   * @return \Drupal\Core\Extension\Extension|null
   *   The module object, or NULL if it does not exist.
   */
  public function getModule(string $name): ?Extension;

  /**
   * Checks if a module exists and is enabled.
   *
   * @param string $module
   *   Module name.
   *
   * @return bool
   *   TRUE if enabled.
   */
  public function moduleExists(string $module): bool;

}
