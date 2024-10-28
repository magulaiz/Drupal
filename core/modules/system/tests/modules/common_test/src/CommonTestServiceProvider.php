<?php

declare(strict_types=1);

namespace Drupal\common_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Hook\HookOrder;

class CommonTestServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    HookOrder::last($container, 'drupal_alter_foo_alter', 'Drupal\\common_test\\Hook\\CommonTestHooks::blockDrupalAlterFooAlter');
  }

}
