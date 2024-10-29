<?php

namespace Drupal\content_translation;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Extension\ProceduralCall;
use Drupal\Core\Hook\HookOrder;

class ContentTranslationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $proceduralCall = ProceduralCall::class . '::';
    HookOrder::last($container, 'entity_type_alter', $proceduralCall . 'content_translation_entity_type_alter');
    HookOrder::first($container, 'entity_bundle_info_alter', $proceduralCall . 'content_translation_entity_bundle_info_alter');
  }

}
