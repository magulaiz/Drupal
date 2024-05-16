<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Extension;

use Drupal\Core\Database\Database;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\Exception\ObsoleteExtensionException;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Tests the ModuleInstaller class.
 *
 * @coversDefaultClass \Drupal\Core\Extension\ModuleInstaller
 *
 * @group Extension
 */
class ModuleInstallerTest extends KernelTestBase {

  /**
   * Tests that routes are rebuilt during install and uninstall of modules.
   *
   * @covers ::install
   * @covers ::uninstall
   */
  public function testRouteRebuild() {
    // Remove the routing table manually to ensure it can be created lazily
    // properly.
    Database::getConnection()->schema()->dropTable('router');

    $this->container->get('module_installer')->install(['router_test']);
    $route = $this->container->get('router.route_provider')->getRouteByName('router_test.1');
    $this->assertEquals('/router_test/test1', $route->getPath());

    $this->container->get('module_installer')->uninstall(['router_test']);
    $this->expectException(RouteNotFoundException::class);
    $this->container->get('router.route_provider')->getRouteByName('router_test.1');
  }

  /**
   * Tests config changes by hook_install() are saved for dependent modules.
   *
   * @covers ::install
   */
  public function testConfigChangeOnInstall() {
    // Install the child module so the parent is installed automatically.
    $this->container->get('module_installer')->install(['module_handler_test_multiple_child']);
    $modules = $this->config('core.extension')->get('module');

    $this->assertArrayHasKey('module_handler_test_multiple', $modules, 'Module module_handler_test_multiple is installed');
    $this->assertArrayHasKey('module_handler_test_multiple_child', $modules, 'Module module_handler_test_multiple_child is installed');
    $this->assertEquals(1, $modules['module_handler_test_multiple'], 'Weight of module_handler_test_multiple is set.');
    $this->assertEquals(1, $modules['module_handler_test_multiple_child'], 'Weight of module_handler_test_multiple_child is set.');
  }

  /**
   * Tests cache bins defined by modules are removed when uninstalled.
   *
   * @covers ::removeCacheBins
   */
  public function testCacheBinCleanup() {
    $schema = $this->container->get('database')->schema();
    $table = 'cache_module_cache_bin';

    $module_installer = $this->container->get('module_installer');
    $module_installer->install(['module_cache_bin']);

    // Prime the bin.
    /** @var \Drupal\Core\Cache\CacheBackendInterface $cache_bin */
    $cache_bin = $this->container->get('module_cache_bin.cache_bin');
    $cache_bin->set('foo', 'bar');

    // A database backend is used so there is a convenient way check whether the
    // backend is uninstalled.
    $this->assertTrue($schema->tableExists($table));

    $module_installer->uninstall(['module_cache_bin']);
    $this->assertFalse($schema->tableExists($table));
  }

  /**
   * Ensure that rebuilding the container in hook_install() works.
   */
  public function testKernelRebuildDuringHookInstall() {
    \Drupal::state()->set('module_test_install:rebuild_container', TRUE);
    $module_installer = $this->container->get('module_installer');
    $this->assertTrue($module_installer->install(['module_test']));
  }

  /**
   * Tests install with a module with an invalid core version constraint.
   *
   * @dataProvider providerTestInvalidCoreInstall
   * @covers ::install
   */
  public function testInvalidCoreInstall($module_name, $install_dependencies) {
    $this->expectException(MissingDependencyException::class);
    $this->expectExceptionMessage("Unable to install modules: module '$module_name' is incompatible with this version of Drupal core.");
    $this->container->get('module_installer')->install([$module_name], $install_dependencies);
  }

  /**
   * Data provider for testInvalidCoreInstall().
   */
  public static function providerTestInvalidCoreInstall() {
    return [
      'no dependencies system_core_incompatible_semver_test' => [
        'system_core_incompatible_semver_test',
        FALSE,
      ],
      'install_dependencies system_core_incompatible_semver_test' => [
        'system_core_incompatible_semver_test',
        TRUE,
      ],
      'no dependencies composer_core_incompatible_test' => [
        'system_core_incompatible_semver_test',
        FALSE,
      ],
      'install_dependencies composer_core_incompatible_test' => [
        'system_core_incompatible_semver_test',
        TRUE,
      ],
    ];
  }

  /**
   * Tests install with a dependency with an invalid core version constraint.
   *
   * @dataProvider providerDependencyInvalidCoreInstall
   * @covers ::install
   */
  public function testDependencyInvalidCoreInstall($module, $dependency) {
    $this->expectException(MissingDependencyException::class);
    $this->expectExceptionMessage("Unable to install modules: module '$module'. Its dependency module '$dependency' is incompatible with this version of Drupal core.");
    $this->container->get('module_installer')->install([$module]);
  }

  /**
   * Dataprovider for testDependencyInvalidCoreInstall().
   */
  public function providerDependencyInvalidCoreInstall() {
    return [
      'info with info.yml dependency' => [
        'system_incompatible_core_version_dependencies_test',
        'system_core_incompatible_semver_test',
      ],
      'composer.json with info.yml dependency' => [
        'composer_incompatible_core_depend_test',
        'system_incompatible_core_version_test',
      ],
      'composer.json with composer.json dependency' => [
        'composer_incompatible_core_composer_depend_test',
        'composer_core_incompatible_test',
      ],
      'info with composer.json dependency' => [
        'composer_incompatible_core_composer_depend_test',
        'composer_core_incompatible_test',
      ],
    ];
  }

  /**
   * Tests invalid core install, without installing dependencies.
   *
   * @dataProvider provideDependencyInvalidCoreInstallNoDependencies
   * @covers ::install
   */
  public function testDependencyInvalidCoreInstallNoDependencies($module) {
    $this->assertTrue($this->container->get('module_installer')->install([$module], FALSE));
  }

  /**
   * Dataprovider for testDependencyInvalidCoreInstallNoDependencies().
   */
  public function provideDependencyInvalidCoreInstallNoDependencies() {
    return [
      'info with info.yml dependency' => [
        'system_incompatible_core_version_dependencies_test',
      ],
      'composer.json with info.yml dependency' => [
        'composer_incompatible_core_depend_test',
      ],
      'composer.json with composer.json dependency' => [
        'composer_incompatible_core_composer_depend_test',
      ],
      'info with composer.json dependency' => [
        'composer_incompatible_core_composer_depend_test',
      ],
    ];
  }

  /**
   * Tests trying to install an obsolete module.
   *
   * @covers ::install
   */
  public function testObsoleteInstall() {
    $this->expectException(ObsoleteExtensionException::class);
    $this->expectExceptionMessage("Unable to install modules: module 'system_status_obsolete_test' is obsolete.");
    $this->container->get('module_installer')->install(['system_status_obsolete_test']);
  }

  /**
   * Tests trying to install a deprecated module.
   *
   * @covers ::install
   *
   * @group legacy
   */
  public function testDeprecatedInstall() {
    $this->expectDeprecation("The module 'deprecated_module' is deprecated. See http://example.com/deprecated");
    \Drupal::service('module_installer')->install(['deprecated_module']);
    $this->assertTrue(\Drupal::service('module_handler')->moduleExists('deprecated_module'));
  }

}
