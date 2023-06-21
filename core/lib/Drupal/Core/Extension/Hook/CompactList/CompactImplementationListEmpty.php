<?php

namespace Drupal\Core\Extension\Hook\CompactList;

use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListEmpty;
use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListEmpty;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Empty list, for a hook with no implementations.
 *
 * This reduces cache storage space and load time.
 */
class CompactImplementationListEmpty implements CompactImplementationListInterface {

  /**
   * {@inheritdoc}
   */
  public function hasImplementations(string|array $modules = NULL): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSingleModuleCallbackList(
    string $module,
    ContainerInterface $container,
    ModuleHandlerInterface $module_handler,
    \Closure $invalidate,
  ): SingleModuleCallbackListInterface {
    return new SingleModuleCallbackListEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function buildCallbackList(
    ContainerInterface $container,
    ModuleHandlerInterface $module_handler,
    \Closure $invalidate,
  ): HookImplementationCallbackListInterface {
    return new HookImplementationCallbackListEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function removeBadImplementations(ContainerInterface $container): void {}

  /**
   * {@inheritdoc}
   */
  public function getPrintableNames(bool $prepend_module = TRUE): array {
    return [];
  }

}
