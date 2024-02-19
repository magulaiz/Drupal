<?php

declare(strict_types = 1);

namespace Drupal\module_discovery_definition_template_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider for Module Discovery Definition Template Test.
 */
final class ModuleDiscoveryDefinitionTemplateTestServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      ->addCompilerPass(new DiscoveryDefinitionTemplateLocatorPass());
  }

}
