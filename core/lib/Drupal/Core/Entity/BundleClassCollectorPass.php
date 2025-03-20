<?php

declare(strict_types=1);

namespace Drupal\Core\Entity;

use Drupal\Core\Entity\Attribute\Bundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects and registers bundle classes.
 *
 * @todo more documentation.
 */
class BundleClassCollectorPass implements CompilerPassInterface {

  /**
   * The bundle classes.
   *
   * @var array<class-string, class-string>
   */
  protected array $bundleClasses = [];

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // @todo should we limit this to modules/profiles?
    $namespaces = $container->getParameter('container.namespaces');
    foreach ($namespaces as $namespace => $directory) {
      if (!file_exists($directory . '/Entity')) {
        continue;
      }
      $this->collectBundleClasses($directory . '/Entity', $namespace, $directory);
    }
    $container->setParameter('entity.bundle_classes', $this->bundleClasses);
  }

  /**
   * Collects bundle classes.
   *
   * @param string $directory
   *   The directory to search for bundle classes.
   * @param string $namespace
   *   The namespace of the directory.
   * @param string $namespace_root
   *   The directory of the namespace root.
   */
  protected function collectBundleClasses(string $directory, string $namespace, string $namespace_root): void {
    $iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS);
    $iterator = new \RecursiveCallbackFilterIterator($iterator, static::filterIterator(...));
    foreach ($iterator as $fileInfo) {
      if ($fileInfo->isDir()) {
        $this->collectBundleClasses($fileInfo->getPathname(), $namespace, $namespace_root);
        continue;
      }
      $subdir = substr($fileInfo->getPath(), strlen($namespace_root));
      $fqcn = str_replace(DIRECTORY_SEPARATOR, "\\", $namespace . $subdir . '\\' . $fileInfo->getBasename('.php'));
      if (!class_exists($fqcn)) {
        continue;
      }
      $reflection = new \ReflectionClass($fqcn);
      if ($reflection->getAttributes(Bundle::class)) {
        $this->bundleClasses[$fqcn] = $fqcn;
      }
    }
  }

  /**
   * Filter iterator callback.
   */
  protected static function filterIterator(\SplFileInfo $fileInfo, $key, \RecursiveDirectoryIterator $iterator): bool {
    return $iterator->isDir() || $fileInfo->getExtension() === 'php';
  }

}
