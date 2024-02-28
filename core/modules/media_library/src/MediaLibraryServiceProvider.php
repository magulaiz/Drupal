<?php

namespace Drupal\media_library;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Registers services in the container.
 */
class MediaLibraryServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->registerForAutoconfiguration(MediaLibraryOpenerInterface::class)
      ->addTag('media_library.opener');
  }

}
