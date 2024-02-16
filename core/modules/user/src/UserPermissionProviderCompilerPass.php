<?php

declare(strict_types = 1);

namespace Drupal\user;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Discovery\YamlDiscovery;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * User permission provider compiler pass.
 */
final class UserPermissionProviderCompilerPass implements CompilerPassInterface {

  /**
   * Container tag for permission providers.
   */
  public const USER_PERMISSION_PROVIDER_SERVICE_TAG = 'user.permission_provider';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Build tagged user permission provider services from YAML:
    /** @var array<string, string> $namespaceDirs */
    $moduleDirectories = array_map(static function (array $moduleData): string {
      /** @var array{type: string, pathname: string, filename: string} $moduleData */
      return DRUPAL_ROOT . '/' . dirname($moduleData['pathname']);
    }, $container->getParameter('container.modules'));

    $discovery = new YamlDiscovery('permissions', $moduleDirectories);
    foreach ($discovery->findAll() as $provider => $permissions) {
      // The top-level 'permissions_callback' is a list of methods in controller
      // syntax, see \Drupal\Core\Controller\ControllerResolver. These methods
      // should return an array of permissions in the same structure.
      /** @var callable-string[] $callbacks */
      $callbacks = $permissions['permission_callbacks'] ?? [];
      foreach ($callbacks as $callback) {
        if (!str_contains($callback, '::')) {
          throw new \RuntimeException('`permission_callbacks` only supports callable in string `ClassOrService::method` format.');
        }

        [$classOrService, $method] = explode('::', $callback, 2);
        if ($container->hasDefinition($classOrService)) {
          $definition = $container->getDefinition($classOrService);
        }
        elseif (class_exists($classOrService)) {
          $definition = (new Definition($classOrService))
            ->setAutoconfigured(TRUE)
            ->setAutowired(TRUE);

          if ((new \ReflectionClass($classOrService))->isSubclassOf(ContainerInjectionInterface::class)) {
            $definition
              ->setFactory([$classOrService, 'create'])
              ->setArguments([new Reference('service_container')]);
          }

          // Drupal does not yet support private-by-default services.
          // @see https://www.drupal.org/project/drupal/issues/3391860
          $definition->setPublic(TRUE);
          $container->setDefinition('.user.permission_provider.' . ContainerBuilder::hash($classOrService . mt_rand()), $definition);
        }
        else {
          throw new \Exception('`permission_callbacks` with class class or service `%s` not found.', $classOrService);
        }

        $definition->addTag(static::USER_PERMISSION_PROVIDER_SERVICE_TAG, [
          'method' => $method,
          'provider' => $provider,
        ]);
      }
    }

    // Build permission provider locator and mapping:
    $references = [];
    $permissionProvidersMapping = [];
    foreach ($container->findTaggedServiceIds(static::USER_PERMISSION_PROVIDER_SERVICE_TAG) as $serviceId => $tags) {
      $methods = array_column($tags, 'method');
      $providers = array_column($tags, 'provider');
      $permissionProvidersMapping[$serviceId] = [
        'methods' => $methods,
        'provider' => reset($providers) ?: throw new \Exception(sprintf('Missing provider tag on permission provider: %s', $serviceId)),
      ];
      $references[$serviceId] = new Reference($serviceId);
    }

    $permissionProvidersLocator = ServiceLocatorTagPass::register($container, $references);
    $container->getDefinition('user.permission_provider_locator')
      ->setArgument('$permissionProvidersMapping', $permissionProvidersMapping)
      ->setArgument('$permissionProvidersLocator', $permissionProvidersLocator);
  }

}
