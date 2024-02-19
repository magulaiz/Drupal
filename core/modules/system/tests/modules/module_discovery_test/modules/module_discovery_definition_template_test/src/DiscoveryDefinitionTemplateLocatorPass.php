<?php

declare(strict_types = 1);

namespace Drupal\module_discovery_definition_template_test;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Module discovery definition template compiler pass.
 */
final class DiscoveryDefinitionTemplateLocatorPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Runtime code can't tell if a service received a tag, use a service
    // locator to bring them together.
    $references = [];
    foreach ($container->findTaggedServiceIds('tag_copied_to_all_services') as $serviceId => $tags) {
      $references[$serviceId] = new Reference($serviceId);
    }
    $container->getDefinition('definition_template_locator')
      ->replaceArgument(0, $references);
  }

}
