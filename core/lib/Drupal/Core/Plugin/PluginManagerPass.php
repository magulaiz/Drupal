<?php

namespace Drupal\Core\Plugin;

@trigger_error('The ' . __NAMESPACE__ . '\PluginManagerPass is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/1234567', E_USER_DEPRECATED);

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers plugin managers to the plugin.cache_clearer service.
 */
class PluginManagerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $cache_clearer_definition = $container->getDefinition('plugin.cache_clearer');
    foreach ($container->getDefinitions() as $service_id => $definition) {
      if (str_starts_with($service_id, 'plugin.manager.') || $definition->hasTag('plugin_manager_cache_clear')) {
        if (is_subclass_of($definition->getClass(), '\Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface')) {
          $cache_clearer_definition->addMethodCall('addCachedDiscovery', [new Reference($service_id)]);
        }
      }
    }
  }

}
