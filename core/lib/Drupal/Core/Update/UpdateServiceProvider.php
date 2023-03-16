<?php

namespace Drupal\Core\Update;

use Drupal\Core\Config\NoSchemaConfig;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Customizes the container for running updates.
 */
class UpdateServiceProvider implements ServiceProviderInterface, ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Decorate the cache factory in order to use
    // \Drupal\Core\Update\UpdateBackend while running updates.
    $container
      ->register('update.cache_factory', UpdateCacheBackendFactory::class)
      ->setDecoratedService('cache_factory')
      ->addArgument(new Reference('update.cache_factory.inner'));

    $container->addCompilerPass(new UpdateCompilerPass(), PassConfig::TYPE_REMOVE, 128);
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // The alias-based processor requires the path_alias entity schema to be
    // installed, so we prevent it from being registered to the path processor
    // manager. We do this by removing the tags that the compiler pass looks
    // for. This means that the URL generator can safely be used during the
    // database update process.
    if ($container->hasDefinition('path_alias.path_processor')) {
      $container->getDefinition('path_alias.path_processor')
        ->clearTag('path_processor_inbound')
        ->clearTag('path_processor_outbound');
    }

    $root = $container->getParameter('app.root');
    require_once $root . '/core/includes/install.inc';
    require_once $root . '/core/includes/update.inc';
    drupal_load_updates();
    if (!empty(update_get_update_list())) {
      // Disable config schema in the config system during hook_update_N updates
      // as config schema can be incorrect at this time.
      // @todo does this actually work here - I think the service might exist
      //   at this point.
      $container->getDefinition('config.factory')->addMethodCall('setMutableConfigClass', [NoSchemaConfig::class]);
    }
  }

}
