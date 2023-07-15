<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\CompactList;

use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;
use Drupal\Core\Extension\ModuleLoaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Implementation list optimized for loading from cache.
 */
interface CompactImplementationListInterface {

  /**
   * Checks if implementations exist for given modules.
   *
   * @param string|list<string>|null $modules
   *   One or more module names, or NULL to check for any implementations.
   *
   * @return bool
   *   TRUE if at least one implementation exists.
   */
  public function hasImplementations(string|array $modules = NULL): bool;

  /**
   * Builds a callback list for a single module.
   *
   * @param string $module
   *   Module name.
   * @param \Psr\Container\ContainerInterface $container
   *   Container.
   * @param \Drupal\Core\Extension\ModuleLoaderInterface $module_loader
   *   Module loader.
   * @param \Closure $invalidate
   *   Callback to be called when the list of implementations changes.
   *
   * @phpstan-param \Closure&(callable(): void) $invalidate
   *
   * @return \Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface
   *   Object with list of callbacks.
   */
  public function buildSingleModuleCallbackList(
    string $module,
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): SingleModuleCallbackListInterface;

  /**
   * Builds a callback list object.
   *
   * The calling code should cache the result, and avoid calling it repeatedly.
   *
   * @param \Psr\Container\ContainerInterface $container
   *   Container.
   * @param \Drupal\Core\Extension\ModuleLoaderInterface $module_loader
   *   Module loader.
   * @param \Closure $invalidate
   *   Callback to call when list of implementations changes.
   *
   * @phpstan-param \Closure&(callable(): void) $invalidate
   *
   * @return \Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackList
   *   List of implementations and module names.
   */
  public function buildCallbackList(
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): HookImplementationCallbackListInterface;

  /**
   * Removes implementations that no longer work.
   *
   * @param \Psr\Container\ContainerInterface $container
   *   Container.
   *
   * @todo Is this ever a good idea?
   */
  public function removeBadImplementations(ContainerInterface $container): void;

  /**
   * Gets printable names for implementations.
   *
   * @param bool $prepend_module
   *   TRUE to prepend "$module: " to each string.
   *
   * @return list<string>
   *   List of implementations suitable for printing.
   *   Format: "$module: $printable_callback", without '()'.
   */
  public function getPrintableNames(bool $prepend_module = TRUE): array;

}
