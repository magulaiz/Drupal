<?php

declare(strict_types=1);

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * System compiler pass.
 *
 * Inspired by:
 *  - \Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass()
 *  - \Symfony\Bundle\FrameworkBundle\Console\Application::registerCommands()
 */
final class DexCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $serviceIds = [];

    // Drupal doesn't have a way of getting all classes with a particular
    // attribute yet, so we do it ourselves.
    // Most of this is copied from the 'sm' project.
    foreach ($this->getClasses($container->getParameter('container.namespaces')) as $className) {
      // Don't create a service definition if this class is already a service.
      if ($container->hasDefinition($className) || $container->hasAlias($className)) {
        continue;
      }

      // Check the full class hierarchy exists, in case the discovered class
      // extends class of optional dependencies, like Drush or Drupal Console.
      $reflection = new \ReflectionClass($className);
      while ($parent = $reflection->getParentClass()) {
        if (!class_exists($parent->getName())) {
          continue;
        }
        $reflection = $parent;
      }

      $definition = new Definition($className);
      $definition
        ->setAutoconfigured(TRUE)
        ->setAutowired(TRUE)
        ->setPublic(TRUE)
        ->setTags(['console.command' => []]);

      $container->setDefinition($className, $definition);
    }

    // Register commands from above to the container.
    $commandServices = $container->findTaggedServiceIds('console.command', TRUE);
    $lazyCommandRefs = [];
    $lazyCommandMap = [];
    foreach ($commandServices as $id => $tags) {
      $definition = $container->getDefinition($id);
      /** @var class-string<\Symfony\Component\Console\Command\Command> $class */
      $class = $container->getParameterBag()->resolveValue($definition->getClass());

      $aliases = $tags[0]['command'] ?? NULL;
      if ($aliases === NULL) {
        if (!$r = $container->getReflectionClass($class)) {
          throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
        }
        if (!$r->isSubclassOf(Command::class)) {
          throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, 'console.command', Command::class));
        }
        $aliases = str_replace('%', '%%', $class::getDefaultName() ?? '');
      }

      $aliases = explode('|', $aliases);
      $commandName = array_shift($aliases);

      if ('' === $commandName) {
        $commandName = array_shift($aliases);
      }

      if (NULL === $commandName) {
        if (!$definition->isPublic()) {
          // If the service is not public, make a public alias.
          $commandId = 'console.command.public_alias.' . $id;
          $container->setAlias($commandId, $id)->setPublic(TRUE);
          $id = $commandId;
        }
        $serviceIds[] = $id;

        continue;
      }

      $definition = $container->getDefinition($id);
      /** @var class-string<\Symfony\Component\Console\Command\Command> $class */
      $class = $container->getParameterBag()->resolveValue($definition->getClass());
      // Pulls in the command name from the PHP attribute:
      $aliases = [str_replace('%', '%%', $class::getDefaultName() ?? '')];
      foreach ($aliases as $alias) {
        $lazyCommandMap[$alias] = $id;
      }
      $lazyCommandRefs[$id] = new Reference($id);
    }

    $container
      ->getDefinition('console.command_loader')
      ->setClass(ContainerCommandLoader::class)
      ->setArguments([ServiceLocatorTagPass::register($container, $lazyCommandRefs), $lazyCommandMap]);

    $container->setParameter('console.command.ids', $serviceIds);
  }

  /**
   * Get command classes for the provided namespaces.
   *
   * @param array<class-string, string> $namespaces
   *   An array of namespaces. Where keys are class strings and values are
   *   paths.
   *
   * @return \Generator<class-string>
   *   Generates class strings.
   *
   * @throws \ReflectionException
   */
  private function getClasses(array $namespaces): \Generator {
    foreach ($namespaces as $namespace => $dirs) {
      $dirs = (array) $dirs;
      foreach ($dirs as $dir) {
        $dir .= '/Command';
        if (!file_exists($dir)) {
          continue;
        }
        $namespace .= '\\Command';

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        /** @var \SplFileInfo $fileinfo */
        foreach ($iterator as $fileinfo) {
          if ($fileinfo->getExtension() !== 'php') {
            continue;
          }

          /** @var \RecursiveDirectoryIterator|null $subDir */
          $subDir = $iterator->getSubIterator();
          if (NULL === $subDir) {
            continue;
          }

          $subDir = $subDir->getSubPath();
          $subDir = $subDir !== '' ? str_replace(DIRECTORY_SEPARATOR, '\\', $subDir) . '\\' : '';
          /** @var class-string $class */
          $class = $namespace . '\\' . $subDir . $fileinfo->getBasename('.php');

          try {
            $reflectionClass = new \ReflectionClass($class);
          }
          catch (\Error) {
            // Skip commands where the hierarchy is unresolvable due to
            // optional dependencies.
            continue;
          }

          if (count($reflectionClass->getAttributes(AsCommand::class)) > 0) {
            yield $class;
          }
        }
      }
    }
  }

}
