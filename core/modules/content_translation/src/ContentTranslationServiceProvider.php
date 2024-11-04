<?php

namespace Drupal\content_translation;

use Drupal\content_translation\Hook\ContentTranslationHooks;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Hook\HookOrder;

class ContentTranslationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $class = ContentTranslationHooks::class . '::';
    HookOrder::last($container, 'entity_type_alter', $class . 'entityTypeAlter');
    HookOrder::first($container, 'entity_bundle_info_alter', $class . 'entityBundleInfoAlter');
  }

}
