<?php

declare(strict_types = 1);

namespace Drupal\module_discovery_attribute_service_test;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Attribute autoconfiguration compiler pass.
 */
final class AttributeAutoconfigurationLocatorPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Runtime code can't tell if a service received a tag, use a service
    // locator to bring them together.
    $references = [];
    foreach ($container->findTaggedServiceIds(AttributeAutoconfigurationCompilerPass::SERVICE_TAG) as $serviceId => $tags) {
      $references[$serviceId] = new Reference($serviceId);
    }
    $container->getDefinition('autoconfigure_attribute_to_service_locator')
      ->replaceArgument(0, $references);
  }

}
