<?php

namespace Drupal\ckeditor5;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Extension\HookHelper;

class Ckeditor5ServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $implementations = $container->getParameter(HookHelper::HOOK_IMPLEMENTATIONS);
    $hook = 'form_filter_format_form_alter';
    // Could be called whatever, there's no magic naming on hook classes and methods
//    $implementations[$hook][Ckeditor5Hooks::class]['formFilterFormatFormAlter']['priority'] = min(
//      $implementations[$hook][MediaHooks::class]['whateverMediaClassTheFormAlterMethod']['priority'] ?? 0,
//      $implementations[$hook][EditorHooks::class]['somemethod']['priority'] ?? 0
//    ) - 1;
    $container->setParameter(HookHelper::HOOK_IMPLEMENTATIONS, $implementations);
  }

}
