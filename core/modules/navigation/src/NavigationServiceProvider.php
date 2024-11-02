<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Hook\HookOrder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service provider for the Navigation module.
 *
 * @internal
 */
final class NavigationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    // If shortcuts module service is available, register our own service.
    if ($container->has('shortcut.lazy_builders')) {
      $container
        ->register('navigation.shortcut_lazy_builder', ShortcutLazyBuilder::class)
        ->addArgument(new Reference('shortcut.lazy_builders'));
    }
  }

  public function alter(ContainerBuilder $container) {
    HookOrder::last($container, 'page_top', 'Drupal\\navigation\\Hook\\NavigationHooks::pageTop');
  }

}
