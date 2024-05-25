<?php

namespace Drupal\Core\Update;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;

/**
 * Provides module updates versions handling.
 */
class UpdateHookRegistry {

  /**
   * Indicates that a module has not been installed yet.
   */
  public const SCHEMA_UNINSTALLED = -1;

  /**
   * A list of enabled modules.
   *
   * @var string[]
   */
  protected $enabledModules;

  /**
   * The key value storage for system.schema.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValue;

  /**
   * The key value storage for system.schema_previously_installed.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $schemaPreviouslyInstalledKeyValue;

  /**
   * A static cache of schema currentVersions per module.
   *
   * Stores schema versions of the modules based on their defined hook_update_N
   * implementations.
   * Example:
   * ```
   * [
   *   'example_module' => [
   *     8000,
   *     8001,
   *     8002
   *   ]
   * ]
   * ```
   *
   * @var int[][]
   * @see \Drupal\Core\Update\UpdateHookRegistry::getAvailableUpdates()
   */
  protected $allAvailableSchemaVersions = [];

  /**
   * Constructs a new UpdateHookRegistry.
   *
   * @param array $module_list
   *   An associative array whose keys are the names of installed modules.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key value factory.
   */
  public function __construct(
    array $module_list,
    KeyValueFactoryInterface $key_value_factory,
  ) {
    $this->enabledModules = array_keys($module_list);
    $this->keyValue = $key_value_factory->get('system.schema');
    $this->schemaPreviouslyInstalledKeyValue = $key_value_factory->get('system.schema_previously_installed');
  }

  /**
   * Returns an array of available schema versions for a module.
   *
   * @param string $module
   *   A module name.
   *
   * @return int[]
   *   An array of available updates sorted by version. Empty array returned if
   *   no updates available.
   */
  public function getAvailableUpdates(string $module): array {
    if (!isset($this->allAvailableSchemaVersions[$module])) {
      $this->allAvailableSchemaVersions[$module] = [];

      foreach ($this->enabledModules as $enabled_module) {
        $this->allAvailableSchemaVersions[$enabled_module] = [];
      }

      // Prepare regular expression to match all possible defined
      // hook_update_N().
      $regexp = '/^(?<module>.+)_update_(?<version>\d+)$/';
      $functions = get_defined_functions();
      // Narrow this down to functions ending with an integer, since all
      // hook_update_N() functions end this way, and there are other
      // possible functions which match '_update_'. We use preg_grep() here
      // since looping through all PHP functions can take significant page
      // execution time and this function is called on every administrative page
      // via system_requirements().
      foreach (preg_grep('/_\d+$/', $functions['user']) as $function) {
        // If this function is a module update function, add it to the list of
        // module updates.
        if (preg_match($regexp, $function, $matches)) {
          $this->allAvailableSchemaVersions[$matches['module']][] = (int) $matches['version'];
        }
      }
      // Ensure that updates are applied in numerical order.
      array_walk(
        $this->allAvailableSchemaVersions,
        static function (&$module_updates) {
          sort($module_updates, SORT_NUMERIC);
        }
      );
    }

    return $this->allAvailableSchemaVersions[$module];
  }

  /**
   * @param string $module
   *   A module name.
   *
   * @return array
   */
  public function getPreviouslyInstalledSchemaVersions(string $module): array {
    $previously_installed_schema_versions = $this->schemaPreviouslyInstalledKeyValue->get($module, FALSE);
    if ($previously_installed_schema_versions === FALSE) {
      $previously_installed_schema_versions = $this->calculatePreviouslyInstalledSchemaVersions($module);
      $this->setPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
      return $previously_installed_schema_versions;
    }
    // If the previously installed list is an empty array, don't bother checking
    // for removed hooks.
    if ($previously_installed_schema_versions) {
      $previously_installed_schema_versions = $this->clearRemovedPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
    }
    return $previously_installed_schema_versions;
  }

  /**
   * Calculates the previously run update hook numbers.
   *
   * Calculates based on the current installed version, assuming we have run
   * all hooks up to and including that version.
   *
   * @param string $module
   *   A module name.
   *
   * @return array
   *   The previously run update hook numbers.
   */
  protected function calculatePreviouslyInstalledSchemaVersions(string $module): array {
    $current_version = $this->getInstalledVersion($module);
    $all_available_versions = $this->getAvailableUpdates($module);
    return array_filter($all_available_versions, function ($version) use ($current_version) {
      return $version <= $current_version;
    });
  }

  /**
   * Removes hooks below the last removed number from the ran updates lists.
   *
   * Given a module and a list of previously run update hook numbers, this will
   * check if any on the list are below the value returned from
   * hook_update_last_removed and remove them from the list.
   *
   * @param string $module
   *   A module name.
   * @param array $previously_installed_schema_versions
   *   A list of previously installed update hook numbers.
   *
   * @return array
   *   The list of update hook numbers with removed updates hooks removed.
   */
  protected function clearRemovedPreviouslyInstalledSchemaVersions(string $module, array $previously_installed_schema_versions): array {
    $last_removed_hook = $module . '_update_last_removed';
    if (function_exists($last_removed_hook)  && $last_removed = call_user_func($last_removed_hook)) {
      if (min($previously_installed_schema_versions) < $last_removed) {
        $previously_installed_schema_versions = array_filter($previously_installed_schema_versions, function ($version) use ($last_removed) {
          return $version > $last_removed;
        });
        $this->setPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
      }
    }
    return $previously_installed_schema_versions;
  }

  /**
   * Returns the currently installed schema version for a module.
   *
   * @param string $module
   *   A module name.
   *
   * @return int
   *   The currently installed schema version, or self::SCHEMA_UNINSTALLED if the
   *   module is not installed.
   */
  public function getInstalledVersion(string $module): int {
    return $this->keyValue->get($module, self::SCHEMA_UNINSTALLED);
  }

  /**
   * Updates the installed version information for a module.
   *
   * @param string $module
   *   A module name.
   * @param int $version
   *   The new schema version.
   *
   * @return self
   *   Returns self to support chained method calls.
   */
  public function setInstalledVersion(string $module, int $version): self {
    $this->keyValue->set($module, $version);
    // If we explicitly set the Installed Version, we know that all update hooks
    // Below that number have been run, and can reset the previously installed
    // list.
    $previously_installed_schema_versions = $this->calculatePreviouslyInstalledSchemaVersions($module)
    $this->setPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
    return $this;
  }

  /**
   * Deletes the installed version information for the module.
   *
   * @param string $module
   *   The module name to delete.
   */
  public function deleteInstalledVersion(string $module): void {
    $this->keyValue->delete($module);
    $this->schemaPreviouslyInstalledKeyValue->delete($module);
  }

  /**
   * Returns the currently installed schema version for all modules.
   *
   * @return int[]
   *   Array of modules as the keys and values as the currently installed
   *   schema version of corresponding module, or self::SCHEMA_UNINSTALLED if the
   *   module is not installed.
   */
  public function getAllInstalledVersions(): array {
    return $this->keyValue->getAll();
  }

  /**
   * @param string $module
   *   A module name.
   * @param int[] $versions
   *   A list of update hooks numbers which have been ran
   * @return void
   */
  public function setPreviouslyInstalledSchemaVersions(string $module, array $versions): void {
    $this->schemaPreviouslyInstalledKeyValue->set($module, $versions);
  }

  /**
   * Returns an organized list of update functions for a set of modules.
   *
   * @param string[] $modules
   *   An array of module names to retrieve updates for.
   *
   * @return string[][]
   *   An array containing all the update functions that should be run for each
   *   module, including all updates that haven't previously been run. The keys
   *   of the array contain the module names, and each value is an ordered array
   *   of update functions, keyed by the update number.
   *
   * @see update_resolve_dependencies()
   */
  public function getUpdateFunctionList(array $modules): array {
    // Go through each module and find all updates that we need (including the
    // first update that was requested and any updates that run after it).
    $update_functions = [];
    foreach ($modules as $module) {
      $update_functions[$module] = [];
      $available_updates = $this->getAvailableUpdates($module);
      $previously_installed_updates = $this->getPreviouslyInstalledSchemaVersions($module);
      $updates = array_diff($available_updates, $previously_installed_updates);
      if ($updates) {
        foreach ($updates as $update) {
          $update_functions[$module][$update] = $module . '_update_' . $update;
        }
      }
    }
    return $update_functions;
  }

}
