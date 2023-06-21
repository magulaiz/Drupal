<?php

namespace Drupal\Tests\Core\Extension;

use Composer\Autoload\ClassLoader;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\Hook\CompactList\CompactImplementationList;
use Drupal\Core\Extension\Hook\HookMap;
use Drupal\Core\Extension\Hook\HookMapInterface;
use Drupal\Core\Extension\Hook\Source\CachedImplementationSource;
use Drupal\Core\Extension\Hook\Source\ImplementationSourceInterface;
use Drupal\Core\Extension\Hook\Source\ServiceMethodAttributeHookDiscovery;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\module_handler_test_attr\Hooks\CustomOrder;
use Drupal\module_handler_test_attr\Hooks\TestHooks;
use Drupal\module_handler_test_attr\Hooks\TypeAlter;
use Drupal\Tests\Traits\ExceptionSerializationTrait;
use Drupal\Tests\UnitTestCase;
use Drupal\TestTools\MockCallQueue;
use Drupal\TestTools\RuntimeAutowireContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\Core\Extension\ModuleHandler
 * @runTestsInSeparateProcesses
 *
 * @group Extension
 */
class ModuleHandlerTest extends UnitTestCase {

  use ExceptionSerializationTrait;

  protected const MODULES_PATH = 'core/tests/Drupal/Tests/Core/Extension/modules';

  protected const TEST_MODULE_PATH = self::MODULES_PATH . '/module_handler_test';

  /**
   * Module names at startup.
   *
   * @var list<string>
   */
  protected array $modules = [
    'module_handler_test',
  ];

  protected array $serviceClassesByModule = [];

  /**
   * @var \Drupal\TestTools\RuntimeAutowireContainer
   */
  protected RuntimeAutowireContainer $container;

  /**
   * @var \Drupal\TestTools\MockCallQueue
   */
  private MockCallQueue $queue;

  /**
   * @var \Composer\Autoload\ClassLoader
   */
  private ClassLoader $classLoader;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp(): void {
    parent::setUp();

    $this->queue = new MockCallQueue();
    $this->classLoader = require $this->root . '/autoload.php';

    $this->container = $this->createContainer();
  }

  /**
   * Creates a runtime container.
   *
   * @return \Drupal\TestTools\RuntimeAutowireContainer
   *   New container.
   */
  protected function createContainer(): RuntimeAutowireContainer {
    $container = new RuntimeAutowireContainer();
    $container->addService($this);
    $container->get(TestCase::class);

    $container->setParameter('string $root', $this->root);
    $container->setParameterCallback('array $module_list', function (): array {
      $list = [];
      foreach ($this->modules as $module) {
        $path = self::MODULES_PATH . '/' . $module;
        if (!is_file($this->root . '/' . $path . '/' . $module . '.info.yml')) {
          // Perhaps this is a core module.
          $path = 'core/modules/' . $module;
          if (!is_file($this->root . '/' . $path . '/' . $module . '.info.yml')) {
            // This is a fake module.
            $path = self::TEST_MODULE_PATH;
          }
        }
        $list[$module] = [
          'type' => 'module',
          'pathname' => $path . '/' . $module . '.info.yml',
          'filename' => NULL,
        ];
        if (is_file($this->root . '/' . $path . '/' . $module . '.module')) {
          $list[$module]['filename'] = $module . '.module';
        }
      }
      return $list;
    });
    $container->setParameterCallback('array $serviceClassesByModule', function (): array {
      $list = array_fill_keys($this->modules, TRUE);
      $list = array_intersect_key($list, $this->serviceClassesByModule);
      $list = array_replace($list, $this->serviceClassesByModule);
      return $list;
    });
    $container->addClass(MemoryBackend::class);
    $container->addClass(HookMap::class);
    $container->addClass(ServiceMethodAttributeHookDiscovery::class);
    $container->addDecoratorClass(CachedImplementationSource::class, [2 => 'hook_implementation_source']);
    $container->addClass(ModuleHandler::class);
    $container->addClass(EventDispatcher::class);

    $container->addService($this->queue);

    $container->addService($this->classLoader);

    return $container;
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    $this->queue->end();
    parent::tearDown();
  }

  /**
   * Adds a test module to the list used in the container.
   *
   * @param string $module
   *   Module name.
   *   This can be an existing test module in the /modules/ subdir, OR it can be
   *   a made-up module name.
   */
  protected function addTestModule(string $module): void {
    $this->modules[] = $module;
    $classes_dir = __DIR__ . '/modules/' . $module . '/src';
    if (is_dir($classes_dir)) {
      $this->classLoader->addPsr4('Drupal\\' . $module . '\\', __DIR__ . '/modules/' . $module . '/src');
    }
  }

  /**
   * Adds a service class that contains hook implementation methods.
   *
   * @param class-string $class
   *   Class to add.
   */
  protected function addHookServiceClass(string $class): void {
    $module = explode('\\', $class, 3)[1];
    $this->container->addClass($class);
    $this->serviceClassesByModule[$module][$class] = $class;
  }

  /**
   * Tests that the runtime container is properly set up.
   */
  public function testContainerSetup(): void {
    $source = $this->container->get(ImplementationSourceInterface::class);
    $this->assertInstanceOf(CachedImplementationSource::class, $source);
  }

  /**
   * Tests loading a module.
   *
   * @covers ::load
   */
  public function testLoadModule() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertFalse(function_exists('module_handler_test_hook'));
    $this->assertTrue($module_handler->load('module_handler_test'));
    $this->assertTrue(function_exists('module_handler_test_hook'));

    $module_handler->addModule('module_handler_test_added', self::MODULES_PATH . '/module_handler_test_added');
    $this->assertFalse(function_exists('module_handler_test_added_hook'), 'Function does not exist before being loaded.');
    $this->assertTrue($module_handler->load('module_handler_test_added'));
    $this->assertTrue(function_exists('module_handler_test_added_helper'), 'Function exists after being loaded.');
    $this->assertTrue($module_handler->load('module_handler_test_added'));

    $this->assertFalse($module_handler->load('module_handler_test_dne'), 'Non-existent modules returns false.');
  }

  /**
   * Tests loading all modules.
   *
   * @covers ::loadAll
   */
  public function testLoadAllModules() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $module_handler->addModule('module_handler_test_all1', self::MODULES_PATH . '/module_handler_test_all1');
    $module_handler->addModule('module_handler_test_all2', self::MODULES_PATH . '/module_handler_test_all2');
    $this->assertFalse(function_exists('module_handler_test_all1_hook'), 'Function does not exist before being loaded.');
    $this->assertFalse(function_exists('module_handler_test_all2_hook'), 'Function does not exist before being loaded.');
    $module_handler->loadAll();
    $this->assertTrue(function_exists('module_handler_test_all1_hook'), 'Function exists after being loaded.');
    $this->assertTrue(function_exists('module_handler_test_all2_hook'), 'Function exists after being loaded.');
  }

  /**
   * Tests reload method.
   *
   * @covers ::reload
   */
  public function testModuleReloading() {
    $module_handler = $this->container->getMock(ModuleHandler::class, ['load']);
    $module_handler_wrapper = $this->queue->wrapMockObject($module_handler)
      ->observeMethod('load');

    // Prepare ->reload().
    $module_handler_wrapper->queueReturn('load', ['module_handler_test'], TRUE);
    $module_handler->reload();
    $this->queue->assertEmpty();

    // Add a module.
    $module_handler->addModule('module_handler_test_added', self::MODULES_PATH . '/module_handler_test_added');

    // Prepare for another ->reload().
    $module_handler_wrapper->queueReturn('load', ['module_handler_test'], TRUE);
    $module_handler_wrapper->queueReturn('load', ['module_handler_test_added'], TRUE);
    $module_handler->reload();
  }

  /**
   * Tests isLoaded accessor.
   *
   * @covers ::isLoaded
   */
  public function testIsLoaded() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertFalse($module_handler->isLoaded());
    $module_handler->loadAll();
    $this->assertTrue($module_handler->isLoaded());
  }

  /**
   * Confirm we get back the modules set in the constructor.
   *
   * @covers ::getModuleList
   */
  public function testGetModuleList() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertEquals($module_handler->getModuleList(), [
      'module_handler_test' => new Extension($this->root, 'module', self::TEST_MODULE_PATH . '/module_handler_test.info.yml', 'module_handler_test.module'),
    ]);
  }

  /**
   * Confirm we get back a module from the module list.
   *
   * @covers ::getModule
   */
  public function testGetModuleWithExistingModule() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertEquals($module_handler->getModule('module_handler_test'), new Extension($this->root, 'module', self::TEST_MODULE_PATH . '/module_handler_test.info.yml', 'module_handler_test.module'));
  }

  /**
   * @covers ::getModule
   */
  public function testGetModuleWithNonExistingModule() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->expectException(UnknownExtensionException::class);
    $module_handler->getModule('claire_alice_watch_my_little_pony_module_that_does_not_exist');
  }

  /**
   * Ensure setting the module list replaces the module list and resets internal structures.
   *
   * @covers ::setModuleList
   */
  public function testSetModuleList() {
    $fixture_module_handler = $this->container->get(ModuleHandler::class);

    // Create a separate container to ensure all objects are distinct.
    $no_modules_container = $this->createContainer();
    // This second container has an empty module list.
    $no_modules_container->setParameter('array $module_list', []);
    $mock_module_handler = $no_modules_container->getMock(ModuleHandler::class, ['resetImplementations']);

    $this->assertCount(0, $mock_module_handler->getModuleList());
    $this->assertCount(1, $fixture_module_handler->getModuleList());

    // Make sure we're starting empty.
    $this->assertEquals([], $mock_module_handler->getModuleList());

    // Ensure that ->getModuleList() triggers ->resetImplementations().
    $mock_module_handler->expects($this->once())->method('resetImplementations');

    // Replace the list with a prebuilt list.
    $mock_module_handler->setModuleList($fixture_module_handler->getModuleList());

    // Ensure those changes are stored.
    $this->assertEquals($fixture_module_handler->getModuleList(), $mock_module_handler->getModuleList());
  }

  /**
   * Tests adding a module.
   *
   * @covers ::addModule
   * @covers ::add
   */
  public function testAddModule() {
    $module_handler = $this->container->getMock(ModuleHandler::class, ['resetImplementations']);

    // Ensure we reset implementations when settings a new modules list.
    $module_handler->expects($this->once())->method('resetImplementations');

    $module_handler->addModule('module_handler_test', self::TEST_MODULE_PATH);
    $this->assertTrue($module_handler->moduleExists('module_handler_test'));
  }

  /**
   * Tests adding a profile.
   *
   * @covers ::addProfile
   * @covers ::add
   */
  public function testAddProfile() {
    $module_handler = $this->container->getMock(ModuleHandler::class, ['resetImplementations']);

    // Ensure we reset implementations when settings a new modules list.
    $module_handler->expects($this->once())->method('resetImplementations');

    // @todo this should probably fail since its a module not a profile.
    $module_handler->addProfile('module_handler_test', self::TEST_MODULE_PATH);
    $this->assertTrue($module_handler->moduleExists('module_handler_test'));
  }

  /**
   * Tests module exists returns correct module status.
   *
   * @covers ::moduleExists
   */
  public function testModuleExists() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertTrue($module_handler->moduleExists('module_handler_test'));
    $this->assertFalse($module_handler->moduleExists('module_handler_test_added'));
  }

  /**
   * @covers ::loadAllIncludes
   */
  public function testLoadAllIncludes() {
    $this->assertTrue(TRUE);
    $module_handler = $this->container->getMock(ModuleHandler::class, ['loadInclude']);

    // Ensure we reset implementations when settings a new modules list.
    $module_handler->expects($this->once())->method('loadInclude');
    $module_handler->loadAllIncludes('hook');
  }

  /**
   * @covers ::loadInclude
   *
   * Note we load code, so isolate the test.
   *
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testLoadInclude() {
    $module_handler = $this->container->get(ModuleHandlerInterface::class);
    // Include exists.
    $this->assertEquals(__DIR__ . '/modules/module_handler_test/hook_include.inc', $module_handler->loadInclude('module_handler_test', 'inc', 'hook_include'));
    $this->assertTrue(function_exists('module_handler_test_hook_include'));
    // Include doesn't exist.
    $this->assertFalse($module_handler->loadInclude('module_handler_test', 'install'));
  }

  /**
   * Tests the ->invoke() method.
   *
   * @covers ::invoke
   */
  public function testInvoke() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertTrue($module_handler->invoke('module_handler_test', 'hook', [TRUE]), 'Module is installed, implementation exists.');
    $this->assertFalse($module_handler->invoke('module_handler_test', 'hook', [FALSE]), 'Module is installed, implementation exists, different argument value.');
    $this->assertNull($module_handler->invoke('module_handler_test', 'hook1', [5]), 'Module is installed, implementation not loaded.');
    $this->assertNull($module_handler->invoke('module_handler_test_new', 'hook', [5]), 'Module is not installed, implementation not loaded.');

    // Files like *.install can be included _after_ initial discovery.
    require_once __DIR__ . '/ModuleHandlerTest.functions.inc';
    // Implementations from the included file now work.
    $this->assertSame(5, $module_handler->invoke('module_handler_test', 'hook1', [5]), 'Module is installed, implementation exists.');
    $this->assertSame(5, $module_handler->invoke('module_handler_test_new', 'hook', [5]), 'Module is not installed, implementation exists.');

    // Test by-reference arguments.
    $values = ['x'];
    $this->assertSame('', $module_handler->invoke('module_handler_test', 'byref', [&$values]));
    $this->assertSameListsOfStrings([
      'x',
      'module_handler_test_byref',
    ], $values);
  }

  public function testInvokeMethodAttributes(): void {
    // Simulate another module being enabled.
    $this->addTestModule('module_handler_test_attr');
    $this->addHookServiceClass(TestHooks::class);

    $module_handler = $this->container->get(ModuleHandler::class);

    // Implementations from the included file now work.
    $this->assertSame(
      [
        'module_handler_test_attr_merge',
        TestHooks::class . '::merge',
        TestHooks::class . '::mergeTwice',
        TestHooks::class . '::mergeTwice',
      ],
      $module_handler->invoke('module_handler_test_attr', 'merge'),
      'Module is installed, implementation exists.',
    );

    // Test by-reference arguments.
    $values = [];
    $this->assertNull($module_handler->invoke(
      'module_handler_test_attr',
      'byref',
      [&$values],
    ));
    $this->assertSameListsOfStrings([
      TestHooks::class . '::byrefNegWeight',
      TestHooks::class . '::byrefAfterOther',
      TestHooks::class . '::byrefBefore',
      'module_handler_test_attr_byref',
      TestHooks::class . '::byref',
      TestHooks::class . '::byref2',
      TestHooks::class . '::byrefAfter',
    ], $values);
  }

  /**
   * Tests implementations methods when module is enabled.
   *
   * @covers ::hasImplementations
   * @covers ::loadAllIncludes
   */
  public function testImplementsHookModuleEnabled() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertTrue($module_handler->hasImplementations('hook', 'module_handler_test'), 'Installed module implementation found.');

    $module_handler->addModule('module_handler_test_added', self::MODULES_PATH . '/module_handler_test_added');
    $this->assertTrue($module_handler->hasImplementations('hook', 'module_handler_test_added'), 'Runtime added module with implementation in include found.');

    $module_handler->addModule('module_handler_test_no_hook', self::MODULES_PATH . '/module_handler_test_no_hook');
    $this->assertFalse($module_handler->hasImplementations('hook', 'module_handler_test_no_hook'), 'Missing implementation not found.');
  }

  /**
   * Tests hasImplementations.
   *
   * @covers ::hasImplementations
   */
  public function testHasImplementations() {
    $mock_hook_map = $this->container->getMock(HookMap::class, [
      'findProceduralImplementations',
    ]);
    $hook_map_wrapper = $this->queue->wrapMockObject($mock_hook_map)
      ->observeMethod('findProceduralImplementations');
    $module_handler = $this->container->get(ModuleHandler::class);

    // Simulate no implementations found.
    $hook_map_wrapper
      ->queueReturn('findProceduralImplementations', ['hook'], [])
      ->queueReturn('findProceduralImplementations', ['module_implements_alter'], []);

    // ModuleHandler::buildImplementationInfo mock returns no implementations.
    $this->assertFalse($module_handler->hasImplementations('hook'));

    $this->queue->assertEmpty();

    // Reset static caches.
    $module_handler->resetImplementations();

    // Simulate one implementation found.
    $hook_map_wrapper
      ->queueReturn('findProceduralImplementations', ['hook'], ['mymodule' => FALSE])
      ->queueReturn('findProceduralImplementations', ['module_implements_alter'], []);

    // ModuleHandler::buildImplementationInfo mock returns an implementation.
    $this->assertTrue($module_handler->hasImplementations('hook'));
  }

  /**
   * Tests getImplementations.
   *
   * @covers ::invokeAllWith
   */
  public function testCachedGetImplementations() {
    $cache_backend = $this->container->getMock(CacheBackendInterface::class);
    $hook_map = $this->container->getMock(HookMap::class, ['findProceduralImplementations']);
    $module_handler = $this->container->getMock(ModuleHandler::class, ['loadInclude']);

    // Observe mocked methods.
    $cache_wrapper = $this->queue->wrapMockObject($cache_backend)
      ->observeMethod('get');
    $this->queue->wrapMockObject($hook_map)
      ->observeMethod('findProceduralImplementations');
    $module_handler_wrapper = $this->queue->wrapMockObject($module_handler)
      ->observeMethod('loadInclude');

    // The ->load() does not trigger any of the observed methods.
    $module_handler->load('module_handler_test');

    $this->queue->assertEmpty();

    // Prepare for ->invokeAllWith().
    // Implementations are loaded from cache.
    // Simulate a warm cache.
    $cache_wrapper->queueReturn(
      'get',
      ['module_implements_cacheable'],
      (object) [
        'data' => [
          'hook' => CompactImplementationList::build('hook')
            ->addProcedural('module_handler_test', 'test')
            ->build(),
        ],
      ],
    );

    // The 'module_handler_test.test.inc' is included.
    $module_handler_wrapper->queueVoid('loadInclude', ['module_handler_test', 'inc', 'module_handler_test.test']);

    $implementors = [];
    $module_handler->invokeAllWith(
      'hook',
      function (callable $hook, string $module) use (&$implementors) {
        $implementors[] = $module;
      }
    );

    $this->assertEquals(['module_handler_test'], $implementors);
  }

  /**
   * Tests getImplementations.
   *
   * @covers ::invokeAllWith
   */
  public function testCachedGetImplementationsMissingMethod() {
    $cache_backend = $this->container->getMock(CacheBackendInterface::class);
    $hook_map = $this->container->getMock(HookMap::class, ['findProceduralImplementations']);
    $module_handler = $this->container->getMock(ModuleHandler::class, ['loadInclude']);

    // Observe mocked methods.
    $cache_wrapper = $this->queue->wrapMockObject($cache_backend)
      ->observeMethods(['get', 'set', 'delete']);
    $this->queue->wrapMockObject($hook_map)
      ->observeMethod('findProceduralImplementations');
    $module_handler_wrapper = $this->queue->wrapMockObject($module_handler)
      ->observeMethod('loadInclude');

    // The ->load() does not trigger any of the observed methods.
    $module_handler->load('module_handler_test');

    $this->queue->assertEmpty();

    // Prepare for ->invokeAllWith().
    // Implementations are loaded from cache.
    // Simulate a warm cache with some missing implementations.
    $cache_wrapper->queueReturn(
      'get',
      ['module_implements_cacheable'],
      (object) [
        'data' => [
          'hook' => CompactImplementationList::build('hook')
            ->addProcedural('module_handler_test', 'test')
            ->addProcedural('module_handler_test_missing')
            ->build(),
        ],
      ],
    );
    // The 'module_handler_test.test.inc' is included.
    $module_handler_wrapper->queueVoid('loadInclude', ['module_handler_test', 'inc', 'module_handler_test.test']);

    $implementors = [];
    $module_handler->invokeAllWith(
      'hook',
      function (callable $hook, string $module) use (&$implementors) {
        $implementors[] = $module;
      }
    );

    $this->assertEquals(['module_handler_test'], $implementors);

    $this->queue->assertEmpty();

    // Prepare for ->writeCache().
    // The 'module_implements_cacheable' cache is updated. The missing
    // implementation is removed.
    $cache_wrapper->queueVoid('set', [
      'module_implements_cacheable',
      [
        'hook' => CompactImplementationList::build('hook')
          ->addProcedural('module_handler_test', 'test')
          ->build(),
      ],
    ], FALSE);

    $hook_map->writeCache();
  }

  /**
   * Tests invoke all.
   *
   * @covers ::invokeAll
   */
  public function testInvokeAll() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $module_handler->addModule('module_handler_test_all1', self::MODULES_PATH . '/module_handler_test_all1');
    $module_handler->addModule('module_handler_test_all2', self::MODULES_PATH . '/module_handler_test_all2');
    $this->assertEquals([TRUE, TRUE, TRUE], $module_handler->invokeAll('hook', [TRUE]));

    // Test by-reference arguments.
    $values = ['x'];
    $this->assertSame(['', 'all1', 'all2'], $module_handler->invokeAll('byref', [&$values]));
    $this->assertSame([
      'x',
      'module_handler_test_byref',
      'module_handler_test_all1_byref',
      'module_handler_test_all2_byref',
    ], $values);
  }

  /**
   * Tests order of implementations in ->invokeAll().
   *
   * @dataProvider providerTestHookOrder
   *
   * @param list<string> $modules
   *   Modules to enable.
   * @param list<string> $expected
   *   Expected functions for ->invokeAll('custom_order').
   * @param list<string> $expected_alter
   *   Expected functions for ->alter('type').
   * @param list<string> $expected_alter_combined
   *   Expected functions for ->alter(['type', 'subtype', 'unaltered']).
   * @param list<string> $expected_alter_combined_2
   *   Expected functions for ->alter(['unaltered', 'subtype']).
   */
  public function testHookOrder(array $modules, array $expected, array $expected_alter, array $expected_alter_combined, array $expected_alter_combined_2): void {
    foreach ($modules as $module) {
      $this->addTestModule($module);
    }
    foreach ([
      CustomOrder::class,
      TypeAlter::class,
    ] as $hook_service_class) {
      $module = explode('\\', $hook_service_class, 3)[1];
      if (in_array($module, $modules)) {
        $this->addHookServiceClass($hook_service_class);
      }
    }
    // Prevent discovery of hook info, to avoid loading fake module files.
    $this->container->get(CacheBackendInterface::class)->set('hook_info', []);

    // Suppress the file_exists() assertion for new Extension objects.
    // This allows to add some fake modules.
    $assertions = ini_get('zend.assertions');
    try {
      if ($assertions > 0) {
        ini_set('zend.assertions', 0);
      }
      $module_handler = $this->container->get(ModuleHandler::class);
    }
    finally {
      if ($assertions > 0) {
        ini_set('zend.assertions', $assertions);
      }
    }

    require_once __DIR__ . '/ModuleHandlerTest.functions.inc';
    require_once __DIR__ . '/ModuleHandlerTest.functions.alter.inc';
    require_once __DIR__ . '/ModuleHandlerTest.functions.module_implements_alter.inc';

    $result = $module_handler->invokeAll('custom_order');
    $this->assertSameListsOfStrings($expected, $result, 'invokeAll(custom_order)');

    $altered = [];
    $module_handler->alter('type', $altered);
    $this->assertSameListsOfStrings($expected_alter, $altered, 'alter(type)');

    $altered = [];
    $module_handler->alter(['type', 'subtype', 'unaltered'], $altered);
    $this->assertSameListsOfStrings($expected_alter_combined, $altered, 'alter([type, subtype, unaltered])');

    // Scenario where the main type order is not altered, but the subtype is.
    $altered = [];
    $module_handler->alter(['unaltered', 'subtype'], $altered);
    $this->assertSameListsOfStrings($expected_alter_combined_2, $altered, 'alter([unaltered, subtype])');
  }

  /**
   * Data provider.
   *
   * @return array
   */
  public function providerTestHookOrder(): array {
    $datasets = [];
    $datasets['basic'] = [
      [
        // Add additional modules with hook implementations.
        // Some of these modules are 'fake', meaning they don't have an actual
        // *.info.yml file, but they do have procedural hook implementations.
        // Note that 'module_handler_test' is already installed.
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
      ],
      [
        // Implementations of hook_custom_order().
        CustomOrder::class . '::negativeWeight',
        'module_handler_test_custom_order',
        CustomOrder::class . '::beforeTest1',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        // Implementations of hook_type_alter().
        'module_handler_test_type_alter',
        'module_handler_test1_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
      ],
      [
        // Implementations for ->alter(['type', 'subtype', 'unaltered'], ..).
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        // Implementations for ->alter(['unaltered', 'subtype'], ..).
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['swapped'] = [
      [
        // Change the order of modules.
        'module_handler_test2',
        'module_handler_test_attr',
        'module_handler_test1',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
        CustomOrder::class . '::attrModule',
        CustomOrder::class . '::beforeTest1',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test1_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
      ],
    ];
    $datasets['last'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() that makes *_test run last.
        'module_handler_test_last',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        CustomOrder::class . '::beforeTest1',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        'module_handler_test_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test1_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
        'module_handler_test_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['test1_last'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() that makes *_test1 run last.
        'module_handler_test1_last',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        'module_handler_test_custom_order',
        CustomOrder::class . '::beforeTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
        'module_handler_test1_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['between'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() to let *_test run after *_test1.
        'module_handler_test_between',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        CustomOrder::class . '::beforeTest1',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        'module_handler_test_custom_order',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['first'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() to let *_test run first.
        'module_handler_test1_first',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        'module_handler_test_custom_order',
        CustomOrder::class . '::beforeTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['remove'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() to remove *_test.
        'module_handler_test_remove',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        'module_handler_test_custom_order',
        CustomOrder::class . '::beforeTest1',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        'module_handler_test_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    // Use hook_module_implements_alter() to insert implementations for fake
    // modules early, late and between.
    // The test covers the _current_ behavior, which might not be ideal in all
    // cases.
    $datasets['fake_extra_modules'] = [
      [
        'module_handler_test1',
        'module_handler_test_attr',
        'module_handler_test2',
        // Add a *_module_implements_alter() to insert fake modules.
        'module_handler_test_fake',
      ],
      [
        CustomOrder::class . '::negativeWeight',
        '_module_handler_test_early_custom_order',
        'module_handler_test_custom_order',
        CustomOrder::class . '::beforeTest1',
        'module_handler_test1_custom_order',
        CustomOrder::class . '::onBehalfOfTest1',
        '_module_handler_test_between_custom_order',
        CustomOrder::class . '::afterTest1',
        CustomOrder::class . '::attrModule',
        'module_handler_test2_custom_order',
        '_module_handler_test_late_custom_order',
        CustomOrder::class . '::positiveWeight',
      ],
      [
        '_module_handler_test_early_type_alter',
        'module_handler_test_type_alter',
        'module_handler_test1_type_alter',
        '_module_handler_test_between_type_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        'module_handler_test2_type_alter',
        '_module_handler_test_late_type_alter',
      ],
      [
        '_module_handler_test_early_type_alter',
        '_module_handler_test_early_subtype_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        '_module_handler_test_between_type_alter',
        '_module_handler_test_between_subtype_alter',
        TypeAlter::class . '::alter',
        TypeAlter::class . '::alterWithHookAttribute',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
        '_module_handler_test_late_type_alter',
        '_module_handler_test_late_subtype_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        TypeAlter::class . '::alterSubtype',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
        '_module_handler_test_early_subtype_alter',
        '_module_handler_test_between_subtype_alter',
        '_module_handler_test_late_subtype_alter',
      ],
    ];
    return $datasets;
  }

  /**
   * Tests a pattern like #[Hook('(node|user)_(update|insert)')].
   */
  public function testHookMultiPattern(): void {
    $this->addTestModule('module_handler_test_attr');
    $this->addHookServiceClass(TestHooks::class);
    $module_handler = $this->container->get(ModuleHandlerInterface::class);
    $results = array_map($module_handler->invokeAll(...), [
      'node_update',
      'user_update',
      'node_insert',
      'user_insert',
      'user_delete',
    ]);
    $this->assertSame([
      [TestHooks::class . '::nodeOrUserUpdateOrInsert'],
      [TestHooks::class . '::nodeOrUserUpdateOrInsert'],
      [TestHooks::class . '::nodeOrUserUpdateOrInsert'],
      [TestHooks::class . '::nodeOrUserUpdateOrInsert'],
      [],
    ], $results);
  }

  /**
   * Tests a pattern like #[Alter('(node|user)_view')].
   */
  public function testAlterHookMultiPattern(): void {
    $this->addTestModule('module_handler_test_attr');
    $this->addHookServiceClass(TestHooks::class);
    $module_handler = $this->container->get(ModuleHandlerInterface::class);
    $results = array_map(
      static function (string $type) use ($module_handler): array {
        $values = [];
        $module_handler->alter($type, $values);
        return $values;
      },
      [
        'node_view',
        'user_view',
        'other_view',
      ]
    );
    $this->assertSame([
      [TestHooks::class . '::nodeOrUserViewAlter'],
      [TestHooks::class . '::nodeOrUserViewAlter'],
      [],
    ], $results);
  }

  /**
   * Tests that write cache calls through to cache library correctly.
   *
   * @covers ::writeCache
   */
  public function testWriteCache(): void {
    $cache_backend = $this->container->getMock(CacheBackendInterface::class);
    $module_handler = $this->container->get(ModuleHandler::class);
    $cache_wrapper = $this->queue->wrapMockObject($cache_backend)
      ->observeMethods(['get', 'set']);

    // Prepare for ->invokeAllWith().
    $cache_wrapper->queueReturn('get', ['module_implements_cacheable'], NULL);
    $cache_wrapper->queueReturn('get', ['hook_info'], NULL);
    $cache_wrapper->queueVoid('set', [
      'hook_info',
      [
        'hook' => ['group' => 'hook'],
      ],
    ]);
    $cache_wrapper->queueReturn('get', ['hook_implementation_source'], NULL);
    $cache_wrapper->queueVoid('set', ['hook_implementation_source', []]);

    $module_handler->invokeAllWith('hook', static function (callable $hook, string $module) {});

    $this->queue->assertEmpty();

    // Prepare for ->writeCache().
    $cache_wrapper->queueVoid('set', [
      'module_implements_cacheable',
      [
        'module_implements_alter' => CompactImplementationList::createEmpty(),
        'hook' => CompactImplementationList::build('hook')
          ->addProcedural('module_handler_test', FALSE)
          ->build(),
      ],
    ], FALSE);

    $hook_map = $this->container->get(HookMapInterface::class);
    $hook_map->writeCache();
  }

  /**
   * Tests hook_hook_info() fetching through getHookInfo().
   *
   * @covers ::getHookInfo
   * @covers \Drupal\Core\Extension\Hook\HookMap::buildHookInfo
   */
  public function testGetHookInfo() {
    $cache_backend = $this->container->getMock(CacheBackendInterface::class);
    $module_handler = $this->container->get(ModuleHandler::class);
    $cache_wrapper = $this->queue->wrapMockObject($cache_backend)
      ->observeMethods(['get']);

    // Prepare for ->getHookInfo().
    // Results are loaded from cache.
    $cache_wrapper->queueReturn('get', ['hook_info'], NULL);

    // The 'real' hook info is discovered and returned.
    $this->assertSame(
      ['hook' => ['group' => 'hook']],
      $module_handler->getHookInfo(),
    );

    $this->queue->assertEmpty();

    // Reset local cache so we get our synthetic result from the cache handler.
    $module_handler->resetImplementations();

    // Prepare for another ->getHookInfo().
    // Simulate a warm cache with synthetic hook info.
    $cache_wrapper->queueReturn('get', ['hook_info'], (object) [
      'data' => ['hook_foo' => ['group' => 'hook']],
    ]);

    // The synthetic info is returned.
    $this->assertEquals(
      ['hook_foo' => ['group' => 'hook']],
      $module_handler->getHookInfo(),
    );
  }

  /**
   * Tests internal implementation cache reset.
   *
   * @covers ::resetImplementations
   */
  public function testResetImplementations() {
    $cache_backend = $this->container->getMock(CacheBackendInterface::class);
    $module_handler = $this->container->get(ModuleHandler::class);

    // Prime local caches.
    $module_handler->invokeAllWith('hook', static function (callable $hook, string $module) {});
    $module_handler->getHookInfo();

    $cache_wrapper = $this->queue->wrapMockObject($cache_backend)
      ->observeMethods(['delete', 'set', 'get']);

    // Prepare for ->resetImplementations().
    // The 'module_implements_cacheable' cache is set to [].
    $cache_wrapper->queueVoid('set', ['module_implements_cacheable', []]);
    // The 'hook_info' cache is deleted.
    $cache_wrapper->queueVoid('delete', ['hook_info']);
    $cache_wrapper->queueVoid('delete', ['hook_implementation_source']);

    // Reset cached implementations.
    $module_handler->resetImplementations();

    $this->queue->assertEmpty();

    // Prepare for ->invokeAllWith().
    // It tries to load 'hook_info' from cache, which is a miss.
    $cache_wrapper->queueReturn('get', ['hook_info'], NULL);
    // New 'hook_info' is discovered and written to the cache.
    $cache_wrapper->queueVoid('set', [
      'hook_info',
      ['hook' => ['group' => 'hook']],
    ]);
    $cache_wrapper->queueReturn('get', ['hook_implementation_source'], NULL);
    $cache_wrapper->queueVoid('set', ['hook_implementation_source', []]);

    $module_handler->invokeAllWith('hook', static function (callable $hook, string $module) {});

    $this->queue->assertEmpty();

    // Prepare for ->writeCache().
    // The newly discovered implementations are written to the cache.
    $cache_wrapper->queueVoid('set', [
      'module_implements_cacheable',
      [
        'module_implements_alter' => CompactImplementationList::createEmpty(),
        'hook' => CompactImplementationList::build('hook')
          ->addProcedural('module_handler_test')
          ->build(),
      ],
    ], FALSE);

    $hook_map = $this->container->get(HookMapInterface::class);
    $hook_map->writeCache();
  }

  /**
   * Tests ->getModuleDirectories().
   *
   * @covers ::getModuleDirectories
   */
  public function testGetModuleDirectories() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertSame(
      ['module_handler_test' => $this->root . '/' . self::TEST_MODULE_PATH],
      $module_handler->getModuleDirectories(),
    );
  }

  /**
   * Tests module directories with a different module list in the constructor.
   *
   * @covers ::getModuleDirectories
   */
  public function testGetModuleDirectories2() {
    $this->modules = ['node', 'system'];
    $module_handler = $this->container->get(ModuleHandler::class);
    $this->assertSame([
      'node' => $this->root . '/core/modules/node',
      'system' => $this->root . '/core/modules/system',
    ], $module_handler->getModuleDirectories());
  }

  /**
   * Tests module directories after modules were added or removed.
   *
   * @covers ::getModuleDirectories
   */
  public function testGetModuleDirectoriesModified() {
    $module_handler = $this->container->get(ModuleHandler::class);
    $module_handler->setModuleList([]);
    $module_handler->addModule('node', 'core/modules/node');
    $this->assertEquals(['node' => $this->root . '/core/modules/node'], $module_handler->getModuleDirectories());
  }

  /**
   * Asserts that two lists of strings are the same, with simplified format.
   *
   * This gets rid of noise due to numbered indices.
   *
   * @param array $expected
   *   Expected value.
   * @param array $actual
   *   Actual value.
   * @param string $message
   *   Message.
   */
  protected function assertSameListsOfStrings(array $expected, array $actual, string $message = '') {
    $this->assertSame(
      "\n" . implode("\n", $expected) . "\n",
      "\n" . implode("\n", $actual) . "\n",
      $message,
    );
    // Make sure that array keys are as expected.
    $this->assertSame($expected, $actual);
  }

}
