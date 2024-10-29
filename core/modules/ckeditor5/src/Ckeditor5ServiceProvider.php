<?php

namespace Drupal\ckeditor5;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Extension\ProceduralCall;
use Drupal\Core\Hook\HookOrder;

class Ckeditor5ServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $proceduralCall = ProceduralCall::class . '::';
    HookOrder::after($container, 'form_filter_format_form_alter', $proceduralCall . 'ckeditor5_form_filter_format_form_alter', $proceduralCall . 'editor_form_filter_format_form_alter', $proceduralCall . 'media_form_filter_format_add_form_alter');
  }

}
