<?php

declare(strict_types = 1);

namespace Drupal\module_discovery_attribute_service_test;

use Drupal\module_discovery_attribute_service_test\Attribute\AttributeToService;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Attribute autoconfiguration compiler pass.
 */
final class AttributeAutoconfigurationCompilerPass implements CompilerPassInterface {

  public const SERVICE_TAG = 'attribute_to_service';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $container->registerAttributeForAutoconfiguration(AttributeToService::class, static function (ChildDefinition $definition, AttributeToService $attribute, \ReflectionClass|\ReflectionMethod $reflector) {
      $tagAttributes = get_object_vars($attribute);
      $definition->addTag(static::SERVICE_TAG, $tagAttributes);
    });
  }

}
