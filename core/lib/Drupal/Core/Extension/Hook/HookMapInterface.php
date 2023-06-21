<?php

namespace Drupal\Core\Extension\Hook;

use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;

interface HookMapInterface {

  /**
   * Checks if implementations exist for given modules.
   *
   * @param string $hook
   *   Hook name.
   * @param string|list<string>|null $modules
   *   One or more module names, or NULL to check for any implementations.
   *
   * @return bool
   *   TRUE if at least one implementation exists.
   */
  public function hasImplementations(string $hook, string|array $modules = NULL): bool;

  /**
   * Gets printable names for implementations.
   *
   * @param string ...$hooks
   *   Hook name(s).
   *
   * @return list<string>
   *   Printable names for implementations.
   */
  public function getPrintableNames(string ...$hooks): array;

  /**
   * Gets a callback list for a single module and hook.
   *
   * @param string $hook
   *   Hook name.
   * @param string $module
   *   Module name.
   *
   * @return \Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface
   *   Callback list.
   */
  public function getSingleModuleCallbackList(string $hook, string $module): SingleModuleCallbackListInterface;

  /**
   * Gets a callback list object for a hook.
   *
   * @param string $hook
   *   Hook name
   * @param string ...$extra_hooks
   *   More hook names.
   *
   * @return \Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface
   */
  public function getCallbackList(string $hook, string ...$extra_hooks): HookImplementationCallbackListInterface;

  /**
   * Gets an object with cacheable hook info.
   *
   * @param string $hook
   *   Hook name.
   * @param string ...$extra_hooks
   *   More hook names.
   *
   * @return \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface
   *   Object with cacheable hook info.
   */
  public function getCacheableList(string $hook, string ...$extra_hooks);

  /**
   * Retrieves a list of hooks that are declared through hook_hook_info().
   *
   * @return array
   *   An associative array whose keys are hook names and whose values are an
   *   associative array containing a group name. The structure of the array
   *   is the same as the return value of hook_hook_info().
   *
   * @see hook_hook_info()
   */
  public function getHookInfo();

}
