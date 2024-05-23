<?php

namespace Drupal\Core\Update;

use Drupal\Core\Extension\ModuleHandlerInterface;
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
  protected $schemaKeyValue;

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
   * A static cache of previously installed scheema Versions.
   *
   * @var int[][]
   */
  protected array $previouslyInstalledSchemaVersions = [];

  /**
   * Constructs a new UpdateHookRegistry.
   *
   * @param array $module_list
   *   An associative array whose keys are the names of installed modules.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key value factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    array $module_list,
    KeyValueFactoryInterface $key_value_factory,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
    $this->enabledModules = array_keys($module_list);
    $this->schemaKeyValue = $key_value_factory->get('system.schema');
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
    if (!isset($this->previouslyInstalledSchemaVersions[$module])) {
      $this->previouslyInstalledSchemaVersions[$module] = $this->schemaPreviouslyInstalledKeyValue->get($module, FALSE);
      if ($this->previouslyInstalledSchemaVersions[$module] === FALSE) {
        $current_version = $this->getInstalledVersion($module);
        $all_available_versions = $this->getAvailableUpdates($module);
        $previously_installed_schema_versions = array_filter($all_available_versions, function ($version) use ($current_version) {
          return $version > $current_version;
        });
        $this->setPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
      }
      elseif ($this->previouslyInstalledSchemaVersions[$module] && $last_removed = $this->moduleHandler->invoke($module, 'update_last_removed')) {
        if (min($this->previouslyInstalledSchemaVersions[$module]) < $last_removed) {
          $previously_installed_schema_versions = array_filter($this->previouslyInstalledSchemaVersions[$module], function ($version) use ($last_removed) {
            return $version <= $last_removed;
          });
          $this->setPreviouslyInstalledSchemaVersions($module, $previously_installed_schema_versions);
        }
      }
    }
    return $this->previouslyInstalledSchemaVersions[$module];
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
    return $this->schemaKeyValue->get($module, self::SCHEMA_UNINSTALLED);
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
    $this->schemaKeyValue->set($module, $version);
    $previously_installed_schema_versions = array_filter($this->getAvailableUpdates($module), function ($available_version) use ($version) {
      return $available_version > $version;
    });
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
    $this->schemaKeyValue->delete($module);
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
    return $this->schemaKeyValue->getAll();
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
    $this->previouslyInstalledSchemaVersions[$module] = $versions;
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
