<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Core\Attribute\Hook\HookAttributeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects services that can have hook implementations.
 */
final class HookPass implements CompilerPassInterface {

  const CONTAINER_PARAMETER_NAME = 'hook_services';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    /** @var array<string, array<string, class-string>> $service_classes_map */
    $service_classes_map = [];
    foreach ($container->findTaggedServiceIds('hooks') as $service_id => $tags) {
      $class = $container->getDefinition($service_id)->getClass();
      if ($class === NULL) {
        throw new \Exception(sprintf('No class name found on tagged service %s.', $service_id));
      }
      $module = NULL;
      foreach ($tags as $tag) {
        if (isset($tag['module'])) {
          $module = $tag['module'];
        }
      }
      if ($module === NULL) {
        $parts = explode('\\', $class, 3);
        if ($parts[0] !== 'Drupal') {
          continue;
        }
        $module = $parts[1];
      }
      $service_classes_map[$module][$service_id] = $class;
    }

    $classes_by_module = $this->scanHooksNamespaces($container->getParameter('container.namespaces'));
    foreach (array_intersect_key($classes_by_module, $service_classes_map) as $module => $classes) {
      $classes_by_module[$module] = array_diff($classes, $service_classes_map[$module]);
    }

    foreach ($classes_by_module as $module => $classes) {
      foreach ($classes as $class) {
        if (!$this->classHasHookAttributes($class)) {
          continue;
        }
        $definition = $this->buildServiceDefinitionFromClass($class);
        $service_id = 'hooks.' . $class;
        $container->setDefinition($service_id, $definition);
        $service_classes_map[$module][$service_id] = $class;
      }
    }

    $container->setParameter(self::CONTAINER_PARAMETER_NAME, $service_classes_map);
  }

  /**
   * Checks if a class has hook attributes.
   *
   * @param class-string $class
   *   Class name.
   *
   * @return bool
   *   TRUE if the class has hook attributes.
   *
   * @throws \ReflectionException
   *   The class does not exist or cannot be loaded.
   */
  private function classHasHookAttributes(string $class): bool {
    $reflectionClass = new \ReflectionClass($class);
    foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
      if ($reflectionMethod->isConstructor()) {
        continue;
      }
      if ($reflectionMethod->getAttributes(
        HookAttributeInterface::class,
        \ReflectionAttribute::IS_INSTANCEOF,
      )) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Builds a service definition from a class.
   *
   * @param class-string $class
   *   Class name.
   *
   * @return \Symfony\Component\DependencyInjection\Definition
   *   Service definition.
   */
  private function buildServiceDefinitionFromClass(string $class): Definition {
    $definition = new Definition($class);

    $definition->addTag('hooks');
    $definition->setPublic(TRUE);

    if (is_a($class, ContainerInjectionInterface::class, TRUE)) {
      $definition->setFactory([$class, 'create']);
      $definition->setArguments([new Reference('service_container')]);
    }
    else {
      $definition->setAutowired(TRUE);
    }

    return $definition;
  }

  /**
   * Get Hux classes for the provided namespaces.
   *
   * @param array<class-string, string> $namespaces
   *   An array of namespaces. Where keys are class strings and values are
   *   paths.
   *
   * @return array<string, list<class-string>>
   *   Generates class strings.
   */
  private function scanHooksNamespaces(array $namespaces): array {
    $classes_by_module = [];
    foreach ($namespaces as $namespace => $dirs) {
      $parts = explode('\\', $namespace, 3);
      if ($parts[0] !== 'Drupal') {
        continue;
      }
      $module = $parts[1];
      $dirs = (array) $dirs;
      foreach ($dirs as $dir) {
        $hooks_dir = $dir . '/Hooks';
        if (!is_dir($hooks_dir)) {
          continue;
        }
        $hooks_namespace = $namespace . '\\Hooks';
        $this->scanNamespaceDir($classes_by_module[$module], $hooks_namespace, $hooks_dir);
      }
    }
    return array_filter($classes_by_module);
  }

  /**
   * Scans a namespace directory for php class files.
   *
   * @param list<class-string> $classes
   *   Map of classes discovered.
   * @param string $namespace
   *   Namespace.
   * @param string $dir
   *   Directory.
   */
  private function scanNamespaceDir(?array &$classes, string $namespace, string $dir): void {
    foreach (scandir($dir) as $candidate) {
      if ($candidate === '.' || $candidate === '..') {
        continue;
      }
      if (str_ends_with($candidate, '.php')) {
        $classes[] = $namespace . '\\' . substr($candidate, 0, -4);
      }
      else {
        $path = $dir . DIRECTORY_SEPARATOR . $candidate;
        if (is_dir($path)) {
          $this->scanNamespaceDir($classes, $namespace . '\\' . $candidate, $path);
        }
      }
    }
  }

}
