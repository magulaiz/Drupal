<?php

namespace Drupal\ckeditor5;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Hook\HookOrder;

class Ckeditor5ServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    HookOrder::after($container, 'form_filter_format_form_alter',
      'Drupal\\ckeditor5\\Hook\\Ckeditor5Hooks::formFilterFormatFormAlter' ,
      'Drupal\\editor\\Hook\\EditorHooks::formFilterFormatFormAlter',
      'Drupal\\media\\Hook\\MediaHooks::formFilterFormatFormAlter');
  }

}
