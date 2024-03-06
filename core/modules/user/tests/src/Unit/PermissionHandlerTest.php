<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Unit;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\PermissionHandler;
use Drupal\user\PermissionProvidersLocator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the permission handler.
 *
 * @group user
 * @group legacy
 *
 * @coversDefaultClass \Drupal\user\PermissionHandler
 * @runTestsInSeparateProcesses
 */
class PermissionHandlerTest extends UnitTestCase {

  /**
   * Provides an extension object for a given module with a human name.
   *
   * @param string $module
   *   The module machine name.
   * @param string $name
   *   The module human name.
   *
   * @return \Drupal\Core\Extension\Extension
   *   The extension object.
   */
  protected function mockModuleExtension($module, $name) {
    $extension = new Extension('vfs:/', $module, "modules/$module");
    $extension->info['name'] = $name;
    return $extension;
  }

  /**
   * Tests permissions provided by YML files.
   *
   * @covers ::__construct
   * @covers ::getPermissions
   * @covers ::buildPermissionsYaml
   * @covers ::moduleProvidesPermissions
   */
  public function testBuildPermissionsYaml() {
    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('modules');
    vfsStreamWrapper::setRoot($root);

    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->once())
      ->method('getModuleDirectories')
      ->willReturn([
        'module_a' => vfsStream::url('modules/module_a'),
        'module_b' => vfsStream::url('modules/module_b'),
        'module_c' => vfsStream::url('modules/module_c'),
      ]);

    $url = vfsStream::url('modules');
    mkdir($url . '/module_a');
    file_put_contents($url . '/module_a/module_a.permissions.yml', "access_module_a: single_description");
    mkdir($url . '/module_b');
    file_put_contents($url . '/module_b/module_b.permissions.yml', <<<EOF
'access module b':
  title: 'Access B'
  description: 'bla bla'
'access module a via module b':
  title: 'Access A via B'
  provider: 'module_a'
EOF
    );
    mkdir($url . '/module_c');
    file_put_contents($url . '/module_c/module_c.permissions.yml', <<<EOF
'access_module_c':
  title: 'Access C'
  description: 'bla bla'
  'restrict access': TRUE
EOF
    );
    $modules = ['module_a', 'module_b', 'module_c'];

    $moduleHandler->expects($this->any())
      ->method('getModuleList')
      ->willReturn(array_flip($modules));
    $moduleExtensionList = $this->createMock(ModuleExtensionList::class);

    $permissionProvidersLocator = new PermissionProvidersLocator([], new ContainerBuilder());
    $permissionHandler = new PermissionHandler($moduleHandler, new TestTranslationManager(), NULL, $moduleExtensionList, $permissionProvidersLocator);

    $actual_permissions = $permissionHandler->getPermissions();
    $this->assertPermissions($actual_permissions);

    $this->assertTrue($permissionHandler->moduleProvidesPermissions('module_a'));
    $this->assertTrue($permissionHandler->moduleProvidesPermissions('module_b'));
    $this->assertTrue($permissionHandler->moduleProvidesPermissions('module_c'));
    $this->assertFalse($permissionHandler->moduleProvidesPermissions('module_d'));
  }

  /**
   * Tests permissions sort inside a module.
   *
   * @covers ::__construct
   * @covers ::getPermissions
   * @covers ::buildPermissionsYaml
   * @covers ::sortPermissions
   */
  public function testBuildPermissionsSortPerModule() {
    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('modules');
    vfsStreamWrapper::setRoot($root);

    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->once())
      ->method('getModuleDirectories')
      ->willReturn([
        'module_a' => vfsStream::url('modules/module_a'),
        'module_b' => vfsStream::url('modules/module_b'),
        'module_c' => vfsStream::url('modules/module_c'),
      ]);

    $moduleExtensionList = $this->createMock(ModuleExtensionList::class);
    $moduleExtensionList->expects($this->exactly(3))
      ->method('getName')
      ->willReturnMap([
        ['module_a', 'Module a'],
        ['module_b', 'Module b'],
        ['module_c', 'A Module'],
      ]);

    $url = vfsStream::url('modules');
    mkdir($url . '/module_a');
    file_put_contents($url . '/module_a/module_a.permissions.yml', <<<EOF
access_module_a2: single_description2
access_module_a1: single_description1
EOF
    );
    mkdir($url . '/module_b');
    file_put_contents($url . '/module_b/module_b.permissions.yml',
      "access_module_a3: single_description"
    );
    mkdir($url . '/module_c');
    file_put_contents($url . '/module_c/module_c.permissions.yml',
      "access_module_a4: single_description"
    );

    $modules = ['module_a', 'module_b', 'module_c'];
    $moduleHandler->expects($this->once())
      ->method('getModuleList')
      ->willReturn(array_flip($modules));

    $permissionProvidersLocator = new PermissionProvidersLocator([], new ContainerBuilder());
    $permissionHandler = new PermissionHandler($moduleHandler, new TestTranslationManager(), NULL, $moduleExtensionList, $permissionProvidersLocator);
    $actual_permissions = $permissionHandler->getPermissions();
    $this->assertEquals(['access_module_a4', 'access_module_a1', 'access_module_a2', 'access_module_a3'],
      array_keys($actual_permissions));
  }

  /**
   * Tests dynamic callback permissions provided by YML files.
   *
   * @covers ::__construct
   * @covers ::getPermissions
   * @covers ::buildPermissionsYaml
   */
  public function testBuildPermissionsYamlCallback() {
    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('modules');
    vfsStreamWrapper::setRoot($root);

    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->once())
      ->method('getModuleDirectories')
      ->willReturn([
        'module_a' => vfsStream::url('modules/module_a'),
        'module_b' => vfsStream::url('modules/module_b'),
        'module_c' => vfsStream::url('modules/module_c'),
      ]);

    $modules = ['module_a', 'module_b', 'module_c'];

    $moduleHandler->expects($this->any())
      ->method('getModuleList')
      ->willReturn(array_flip($modules));

    $moduleExtensionList = $this->createMock(ModuleExtensionList::class);

    $permissionProvidersLocator = new ContainerBuilder();
    $permissionProvidersLocator->set('service1', new TestPermissionCallbacks());
    $permissionProvidersLocator->set('service2', new TestPermissionCallbacks());
    $permissionProvidersLocator->set('service3', new TestPermissionCallbacks());

    $permissionProvidersMapping = [
      'service1' => [
        'methods' => [
          'singleDescription',
        ],
        'provider' => 'module_a',
      ],
      'service2' => [
        'methods' => [
          'titleDescription',
          'titleProvider',
        ],
        'provider' => 'module_b',
      ],
      'service3' => [
        'methods' => [
          'titleDescriptionRestrictAccess',
        ],
        'provider' => 'module_c',
      ],
    ];

    $permissionProvidersLocator = new PermissionProvidersLocator($permissionProvidersMapping, $permissionProvidersLocator);
    $permissionHandler = new PermissionHandler($moduleHandler, new TestTranslationManager(), NULL, $moduleExtensionList, $permissionProvidersLocator);

    $actual_permissions = $permissionHandler->getPermissions();
    $this->assertPermissions($actual_permissions);
  }

  /**
   * Tests a YAML file containing both static permissions and a callback.
   *
   * @legacy
   */
  public function testPermissionsYamlStaticAndCallback() {
    $this->expectDeprecation('permission_callbacks in module_a.permissions.yml is deprecated in drupal:10.3.0 and must be converted to permission providers in drupal:11.0.0. See https://www.drupal.org/node/3421580');

    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('modules');
    vfsStreamWrapper::setRoot($root);

    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->once())
      ->method('getModuleDirectories')
      ->willReturn([
        'module_a' => vfsStream::url('modules/module_a'),
      ]);

    $url = vfsStream::url('modules');
    mkdir($url . '/module_a');
    file_put_contents($url . '/module_a/module_a.permissions.yml', <<<PERMISSIONS
      'access module a':
        title: 'Access A'
        description: 'bla bla'
      permission_callbacks:
        - 'Drupal\\user\\Tests\\TestPermissionCallbacks::titleDescription'
      PERMISSIONS);

    $modules = ['module_a'];

    $moduleHandler->expects($this->any())
      ->method('getModuleList')
      ->willReturn(array_flip($modules));

    $moduleExtensionList = $this->createMock(ModuleExtensionList::class);

    $permissionProvidersLocator = new ContainerBuilder();
    $permissionProvidersLocator->set('service1', new TestPermissionCallbacks());

    $permissionProvidersMapping = [
      'service1' => [
        'methods' => [
          'titleDescription',
        ],
        'provider' => 'module_a',
      ],
    ];

    $permissionProvidersLocator = new PermissionProvidersLocator($permissionProvidersMapping, $permissionProvidersLocator);
    $permissionHandler = new PermissionHandler($moduleHandler, new TestTranslationManager(), NULL, $moduleExtensionList, $permissionProvidersLocator);

    $actual_permissions = $permissionHandler->getPermissions();

    $this->assertCount(2, $actual_permissions);
    $this->assertEquals('Access A', $actual_permissions['access module a']['title']);
    $this->assertEquals('module_a', $actual_permissions['access module a']['provider']);
    $this->assertEquals('bla bla', $actual_permissions['access module a']['description']);
    $this->assertEquals('Access B', $actual_permissions['access module b']['title']);
    $this->assertEquals('module_a', $actual_permissions['access module b']['provider']);
    $this->assertEquals('bla bla', $actual_permissions['access module b']['description']);
  }

  /**
   * Checks that the permissions are like expected.
   *
   * @param array $actual_permissions
   *   The actual permissions
   *
   * @internal
   */
  protected function assertPermissions(array $actual_permissions): void {
    $this->assertCount(4, $actual_permissions);
    $this->assertEquals('single_description', $actual_permissions['access_module_a']['title']);
    $this->assertEquals('module_a', $actual_permissions['access_module_a']['provider']);
    $this->assertEquals('Access B', $actual_permissions['access module b']['title']);
    $this->assertEquals('module_b', $actual_permissions['access module b']['provider']);
    $this->assertEquals('Access C', $actual_permissions['access_module_c']['title']);
    $this->assertEquals('module_c', $actual_permissions['access_module_c']['provider']);
    $this->assertTrue($actual_permissions['access_module_c']['restrict access']);
    $this->assertEquals('module_a', $actual_permissions['access module a via module b']['provider']);
  }

}

class TestPermissionCallbacks {

  public function singleDescription() {
    return [
      'access_module_a' => 'single_description',
    ];
  }

  public function titleDescription() {
    return [
      'access module b' => [
        'title' => 'Access B',
        'description' => 'bla bla',
      ],
    ];
  }

  public function titleDescriptionRestrictAccess() {
    return [
      'access_module_c' => [
        'title' => 'Access C',
        'description' => 'bla bla',
        'restrict access' => TRUE,
      ],
    ];
  }

  public function titleProvider() {
    return [
      'access module a via module b' => [
        'title' => 'Access A via B',
        'provider' => 'module_a',
      ],
    ];
  }

}

/**
 * Implements a translation manager in tests.
 */
class TestTranslationManager implements TranslationInterface {

  /**
   * {@inheritdoc}
   */
  public function translate($string, array $args = [], array $options = []) {
    return new TranslatableMarkup($string, $args, $options, $this);
  }

  /**
   * {@inheritdoc}
   */
  public function translateString(TranslatableMarkup $translated_string) {
    return $translated_string->getUntranslatedString();
  }

  /**
   * {@inheritdoc}
   */
  public function formatPlural($count, $singular, $plural, array $args = [], array $options = []) {
    return new PluralTranslatableMarkup($count, $singular, $plural, $args, $options, $this);
  }

}
