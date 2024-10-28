<?php

declare(strict_types=1);

namespace Drupal\layout_builder_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Hook\HookOrder;

class LayoutBuilderTestServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    HookOrder::before($container, 'system_breadcrumb_alter', 'Drupal\\layout_builder_test\\Hook\\LayoutBuilderTestHooks::systemBreadcrumbAlter', 'Drupal\\layout_builder\\Hook\\LayoutBuilderHooks::systemBreadcrumbAlter');
  }

}
