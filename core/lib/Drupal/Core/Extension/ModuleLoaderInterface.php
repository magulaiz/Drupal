<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

/**
 * Interface for a module loader.
 */
interface ModuleLoaderInterface {

  /**
   * Includes a module's .module file.
   *
   * This prevents including a module more than once.
   *
   * @param string $name
   *   The name of the module to load.
   *
   * @return bool
   *   TRUE if the item is loaded or has already been loaded.
   */
  public function load(string $name): bool;

  /**
   * Loads all enabled modules.
   */
  public function loadAll(): void;

  /**
   * Returns whether all modules have been loaded.
   *
   * @return bool
   *   A Boolean indicating whether all modules have been loaded. This means all
   *   modules; the load status of bootstrap modules cannot be checked.
   */
  public function isLoaded(): bool;

  /**
   * Reloads all enabled modules.
   */
  public function reload(): void;

  /**
   * Loads an include file for each enabled module.
   *
   * @param string $type
   *   The include file's type (file extension).
   * @param string $name
   *   (optional) The base file name (without the $type extension). If omitted,
   *   each module's name is used; i.e., "$module.$type" by default.
   */
  public function loadAllIncludes(string $type, string $name = NULL): void;

  /**
   * Loads a module include file.
   *
   * Examples:
   * @code
   *   // Load node.admin.inc from the node module.
   *   $this->loadInclude('node', 'inc', 'node.admin');
   *   // Load content_types.inc from the node module.
   *   $this->loadInclude('node', 'inc', 'content_types');
   * @endcode
   *
   * @param string $module
   *   The module to which the include file belongs.
   * @param string $type
   *   The include file's type (file extension).
   * @param string|null $name
   *   (optional) The base file name (without the $type extension). If omitted,
   *   $module is used; i.e., resulting in "$module.$type" by default.
   *
   * @return string|false
   *   The name of the included file, if successful; FALSE otherwise.
   */
  public function loadInclude(string $module, string $type, string $name = NULL): string|false;

}
