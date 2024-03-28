<?php

declare(strict_types = 1);

namespace Drupal\module_discovery_attribute_service_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider for Module Discovery Test.
 */
final class ModuleDiscoveryAttributeServiceTestServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      // Priority 200 to execute before AttributeAutoconfigurationPass which has
      // priority 100. See symfony/dependency-injection/Compiler/PassConfig.php.
      ->addCompilerPass(new AttributeAutoconfigurationCompilerPass(), priority: 200)
      ->addCompilerPass(new AttributeAutoconfigurationLocatorPass());
  }

}
