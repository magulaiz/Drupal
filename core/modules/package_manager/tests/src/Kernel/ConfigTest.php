<?php

declare(strict_types=1);

namespace Drupal\Tests\package_manager\Kernel;

use Drupal\Core\Config\StorageManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use PhpTuf\ComposerStager\API\Finder\Service\ExecutableFinderInterface;

/**
 * @group package_manager
 * @internal
 */
class ConfigTest extends PackageManagerKernelTestBase {

  /**
   * Tests that Package Manager auto-detects executable paths on install.
   */
  public function testExecutablePathsAutoDetection(): void {
    $this->container->set(ExecutableFinderInterface::class, new class () implements ExecutableFinderInterface {

      /**
       * {@inheritdoc}
       */
      public function find(string $name): string {
        return match ($name) {
          'composer' => '/fake/path/to/composer',
          'rsync' => '/fake/path/to/rsync',
        };
      }

    });

    $executables = $this->config('package_manager.settings')
      ->get('executables');
    $this->assertNull($executables['composer']);
    $this->assertNull($executables['rsync']);

    $this->container->get(ModuleHandlerInterface::class)
      ->loadInclude('package_manager', 'install');
    package_manager_install();

    $executables = $this->config('package_manager.settings')
      ->get('executables');
    $this->assertSame('/fake/path/to/composer', $executables['composer']);
    $this->assertSame('/fake/path/to/rsync', $executables['rsync']);

    // The executable paths should be removed from exported config.
    /** @var \Drupal\Core\Config\StorageInterface $export */
    $export = $this->container->get(StorageManagerInterface::class)
      ->getStorage();
    $exported_settings = $export->read('package_manager.settings');
    $this->assertIsArray($exported_settings);
    $this->assertNull($exported_settings['executables']['composer']);
    $this->assertNull($exported_settings['executables']['rsync']);
  }

}
