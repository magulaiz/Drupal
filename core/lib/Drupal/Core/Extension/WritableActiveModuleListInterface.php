<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

/**
 * Interface for a writable active module list.
 */
interface WritableActiveModuleListInterface extends ActiveModuleListInterface {

  /**
   * Sets an explicit list of currently active modules.
   *
   * @param array<string, \Drupal\Core\Extension\Extension> $modules
   *   An associative array whose keys are the names of the modules and whose
   *   values are Extension objects.
   */
  public function setModules(array $modules): void;

  /**
   * Adds a module to the list of currently active modules.
   *
   * @param string $name
   *   The module name; e.g., 'node'.
   * @param string $path
   *   The module path; e.g., 'core/modules/node'.
   */
  public function addModule(string $name, string $path): void;

  /**
   * Adds an installation profile to the list of currently active modules.
   *
   * @param string $name
   *   The profile name; e.g., 'standard'.
   * @param string $path
   *   The profile path; e.g., 'core/profiles/standard'.
   */
  public function addProfile(string $name, string $path): void;

}
