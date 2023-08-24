<?php

namespace Drupal\Core\Extension\Hook\CompactList;

use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;
use Drupal\Core\Extension\ModuleLoaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Decorator that includes module files before creating the callbacks.
 */
class ModuleIncludeDecorator implements CompactImplementationListInterface {

  /**
   * Include file groups.
   *
   * @var array<string, array<string, true>>
   */
  private array $includeFileGroups;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface $decorated
   *   Decorated list.
   */
  public function __construct(
    private readonly CompactImplementationListInterface $decorated,
  ) {}

  /**
   * Adds an include file group.
   *
   * @param string $module
   *   Module name.
   * @param string $group
   *   Include file group name.
   */
  public function addIncludeFileGroup(string $module, string $group): void {
    $this->includeFileGroups[$module][$group] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasImplementations(string|array $modules = NULL): bool {
    return $this->decorated->hasImplementations($modules);
  }

  /**
   * {@inheritdoc}
   */
  public function buildSingleModuleCallbackList(
    string $module,
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): SingleModuleCallbackListInterface {
    foreach ($this->includeFileGroups[$module] ?? [] as $group => $true) {
      $module_loader->loadInclude($module, 'inc', "$module.$group");
    }
    return $this->decorated->buildSingleModuleCallbackList($module, $container, $module_loader, $invalidate);
  }

  /**
   * {@inheritdoc}
   */
  public function buildCallbackList(
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): HookImplementationCallbackListInterface {
    foreach ($this->includeFileGroups as $module => $groups) {
      foreach ($groups as $group => $true) {
        $module_loader->loadInclude($module, 'inc', "$module.$group");
      }
    }
    return $this->decorated->buildCallbackList($container, $module_loader, $invalidate);
  }

  /**
   * {@inheritdoc}
   */
  public function removeBadImplementations(ContainerInterface $container): void {
    $this->decorated->removeBadImplementations($container);
  }

  /**
   * {@inheritdoc}
   */
  public function getPrintableNames(bool $prepend_module = TRUE): array {
    return $this->decorated->getPrintableNames($prepend_module);
  }

}
