<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Extension\ModuleHandler
 * @runTestsInSeparateProcesses
 *
 * @group Extension
 */
class ModuleHandlerTest extends UnitTestCase {

  protected const MODULES_PATH = 'core/tests/Drupal/Tests/Core/Extension/modules';

  protected const TEST_MODULE_PATH = self::MODULES_PATH . '/module_handler_test';

  /**
   * The mocked cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheBackend;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp(): void {
    parent::setUp();
    // We can mock the cache handler here, but not the module handler.
    $this->cacheBackend = $this->createMock(CacheBackendInterface::class);
  }

  /**
   * Get a module handler object to test.
   *
   * Since we have to run these tests in separate processes, we have to use
   * test objects which are serializable. Since ModuleHandler will populate
   * itself with Extension objects, and since Extension objects will try to
   * access DRUPAL_ROOT when they're unserialized, we can't store our mocked
   * ModuleHandler objects as a property in unit tests. They must be generated
   * by the test method by calling this method.
   *
   * @return \Drupal\Core\Extension\ModuleHandler
   *   The module handler to test.
   */
  protected function getModuleHandler() {
    $module_handler = new ModuleHandler($this->root, [
      'module_handler_test' => [
        'type' => 'module',
        'pathname' => self::TEST_MODULE_PATH . '/module_handler_test.info.yml',
        'filename' => 'module_handler_test.module',
      ],
    ], $this->cacheBackend);
    return $module_handler;
  }

  /**
   * Tests loading a module.
   *
   * @covers ::load
   */
  public function testLoadModule() {
    $module_handler = $this->getModuleHandler();
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
    $module_handler = $this->getModuleHandler();
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
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root,
        [
          'module_handler_test' => [
            'type' => 'module',
            'pathname' => self::TEST_MODULE_PATH . '/module_handler_test.info.yml',
            'filename' => 'module_handler_test.module',
          ],
        ], $this->cacheBackend,
      ])
      ->onlyMethods(['load'])
      ->getMock();
    $module_handler->expects($this->exactly(3))
      ->method('load')
      ->withConsecutive(
        // First reload.
        ['module_handler_test'],
        // Second reload.
        ['module_handler_test'],
        ['module_handler_test_added'],
      );
    $module_handler->reload();
    $module_handler->addModule('module_handler_test_added', self::MODULES_PATH . '/module_handler_test_added');
    $module_handler->reload();
  }

  /**
   * Tests isLoaded accessor.
   *
   * @covers ::isLoaded
   */
  public function testIsLoaded() {
    $module_handler = $this->getModuleHandler();
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
    $this->assertEquals($this->getModuleHandler()->getModuleList(), [
      'module_handler_test' => new Extension($this->root, 'module', self::TEST_MODULE_PATH . '/module_handler_test.info.yml', 'module_handler_test.module'),
    ]);
  }

  /**
   * Confirm we get back a module from the module list.
   *
   * @covers ::getModule
   */
  public function testGetModuleWithExistingModule() {
    $this->assertEquals($this->getModuleHandler()->getModule('module_handler_test'), new Extension($this->root, 'module', self::TEST_MODULE_PATH . '/module_handler_test.info.yml', 'module_handler_test.module'));
  }

  /**
   * @covers ::getModule
   */
  public function testGetModuleWithNonExistingModule() {
    $this->expectException(UnknownExtensionException::class);
    $this->getModuleHandler()->getModule('claire_alice_watch_my_little_pony_module_that_does_not_exist');
  }

  /**
   * Ensure setting the module list replaces the module list and resets internal structures.
   *
   * @covers ::setModuleList
   */
  public function testSetModuleList() {
    $fixture_module_handler = $this->getModuleHandler();
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root, [], $this->cacheBackend,
      ])
      ->onlyMethods(['resetImplementations'])
      ->getMock();

    // Ensure we reset implementations when settings a new modules list.
    $module_handler->expects($this->once())->method('resetImplementations');

    // Make sure we're starting empty.
    $this->assertEquals([], $module_handler->getModuleList());

    // Replace the list with a prebuilt list.
    $module_handler->setModuleList($fixture_module_handler->getModuleList());

    // Ensure those changes are stored.
    $this->assertEquals($fixture_module_handler->getModuleList(), $module_handler->getModuleList());
  }

  /**
   * Tests adding a module.
   *
   * @covers ::addModule
   * @covers ::add
   */
  public function testAddModule() {

    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root, [], $this->cacheBackend,
      ])
      ->onlyMethods(['resetImplementations'])
      ->getMock();

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

    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root, [], $this->cacheBackend,
      ])
      ->onlyMethods(['resetImplementations'])
      ->getMock();

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
    $module_handler = $this->getModuleHandler();
    $this->assertTrue($module_handler->moduleExists('module_handler_test'));
    $this->assertFalse($module_handler->moduleExists('module_handler_test_added'));
  }

  /**
   * @covers ::loadAllIncludes
   */
  public function testLoadAllIncludes() {
    $this->assertTrue(TRUE);
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root,
        [
          'module_handler_test' => [
            'type' => 'module',
            'pathname' => self::TEST_MODULE_PATH . '/module_handler_test.info.yml',
            'filename' => 'module_handler_test.module',
          ],
        ], $this->cacheBackend,
      ])
      ->onlyMethods(['loadInclude'])
      ->getMock();

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
    $module_handler = $this->getModuleHandler();
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
    $module_handler = $this->getModuleHandler();
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
    $this->assertSame([
      'x',
      'module_handler_test_byref',
    ], $values);
  }

  /**
   * Tests implementations methods when module is enabled.
   *
   * @covers ::hasImplementations
   * @covers ::loadAllIncludes
   */
  public function testImplementsHookModuleEnabled() {
    $module_handler = $this->getModuleHandler();
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
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([$this->root, [], $this->cacheBackend])
      ->onlyMethods(['buildImplementationInfo'])
      ->getMock();
    $module_handler->expects($this->exactly(2))
      ->method('buildImplementationInfo')
      ->with('hook')
      ->willReturnOnConsecutiveCalls(
        [],
        ['mymodule' => FALSE],
      );

    // ModuleHandler::buildImplementationInfo mock returns no implementations.
    $this->assertFalse($module_handler->hasImplementations('hook'));

    // Reset static caches.
    $module_handler->resetImplementations();

    // ModuleHandler::buildImplementationInfo mock returns an implementation.
    $this->assertTrue($module_handler->hasImplementations('hook'));
  }

  /**
   * Tests getImplementations.
   *
   * @covers ::invokeAllWith
   */
  public function testCachedGetImplementations() {
    $this->cacheBackend->expects($this->exactly(1))
      ->method('get')
      ->will($this->onConsecutiveCalls(
        (object) ['data' => ['hook' => ['module_handler_test' => 'test']]]
      ));

    // Ensure buildImplementationInfo doesn't get called and that we work off cached results.
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root, [
          'module_handler_test' => [
            'type' => 'module',
            'pathname' => self::TEST_MODULE_PATH . '/module_handler_test.info.yml',
            'filename' => 'module_handler_test.module',
          ],
        ], $this->cacheBackend,
      ])
      ->onlyMethods(['buildImplementationInfo', 'loadInclude'])
      ->getMock();
    $module_handler->load('module_handler_test');

    $module_handler->expects($this->never())->method('buildImplementationInfo');
    $module_handler->expects($this->once())->method('loadInclude');
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
    $this->cacheBackend->expects($this->exactly(1))
      ->method('get')
      ->will($this->onConsecutiveCalls((object) [
        'data' => [
          'hook' => [
            'module_handler_test' => [],
            'module_handler_test_missing' => [],
          ],
        ],
      ]));

    // Ensure buildImplementationInfo doesn't get called and that we work off cached results.
    $module_handler = $this->getMockBuilder(ModuleHandler::class)
      ->setConstructorArgs([
        $this->root, [
          'module_handler_test' => [
            'type' => 'module',
            'pathname' => self::TEST_MODULE_PATH . '/module_handler_test.info.yml',
            'filename' => 'module_handler_test.module',
          ],
        ], $this->cacheBackend,
      ])
      ->onlyMethods(['buildImplementationInfo'])
      ->getMock();
    $module_handler->load('module_handler_test');

    $module_handler->expects($this->never())->method('buildImplementationInfo');
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
   * Tests invoke all.
   *
   * @covers ::invokeAll
   */
  public function testInvokeAll() {
    $module_handler = $this->getModuleHandler();
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
    $module_handler = $this->getModuleHandler();
    require_once __DIR__ . '/ModuleHandlerTest.functions.inc';
    require_once __DIR__ . '/ModuleHandlerTest.functions.alter.inc';
    require_once __DIR__ . '/ModuleHandlerTest.functions.module_implements_alter.inc';

    // Within this test it is ok to add mismatching extension objects.
    $fake_module_object = new Extension($this->root, 'module', self::TEST_MODULE_PATH . '/module_handler_test.info.yml');
    $module_list = $module_handler->getModuleList();
    foreach ($modules as $module) {
      $module_list[$module] = $fake_module_object;
    }
    $module_handler->setModuleList($module_list);

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
        'module_handler_test2',
      ],
      [
        // Implementations of hook_custom_order().
        'module_handler_test_custom_order',
        'module_handler_test1_custom_order',
        'module_handler_test2_custom_order',
      ],
      [
        // Implementations of hook_type_alter().
        'module_handler_test_type_alter',
        'module_handler_test1_type_alter',
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
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['swapped'] = [
      [
        // Change the order of modules.
        'module_handler_test2',
        'module_handler_test1',
      ],
      [
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
        'module_handler_test1_custom_order',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
        'module_handler_test1_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
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
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
      ],
    ];
    $datasets['last'] = [
      [
        'module_handler_test1',
        'module_handler_test2',
        // Add a *_module_implements_alter() that makes *_test run last.
        'module_handler_test_last',
      ],
      [
        'module_handler_test1_custom_order',
        'module_handler_test2_custom_order',
        'module_handler_test_custom_order',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test2_type_alter',
        'module_handler_test_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
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
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['test1_last'] = [
      [
        'module_handler_test1',
        'module_handler_test2',
        // Add a *_module_implements_alter() that makes *_test1 run last.
        'module_handler_test1_last',
      ],
      [
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
        'module_handler_test1_custom_order',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
        'module_handler_test1_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
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
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['between'] = [
      [
        'module_handler_test1',
        'module_handler_test2',
        // Add a *_module_implements_alter() to let *_test run after *_test1.
        'module_handler_test_between',
      ],
      [
        'module_handler_test1_custom_order',
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['first'] = [
      [
        'module_handler_test1',
        'module_handler_test2',
        // Add a *_module_implements_alter() to let *_test run first.
        'module_handler_test1_first',
      ],
      [
        'module_handler_test1_custom_order',
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    $datasets['remove'] = [
      [
        'module_handler_test1',
        'module_handler_test2',
        // Add a *_module_implements_alter() to remove *_test.
        'module_handler_test_remove',
      ],
      [
        'module_handler_test_custom_order',
        'module_handler_test2_custom_order',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test2_type_alter',
      ],
      [
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
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
        'module_handler_test2',
        // Add a *_module_implements_alter() to insert fake modules.
        'module_handler_test_fake',
      ],
      [
        '_module_handler_test_early_custom_order',
        'module_handler_test_custom_order',
        'module_handler_test1_custom_order',
        '_module_handler_test_between_custom_order',
        'module_handler_test2_custom_order',
        '_module_handler_test_late_custom_order',
      ],
      [
        '_module_handler_test_early_type_alter',
        'module_handler_test_type_alter',
        'module_handler_test1_type_alter',
        '_module_handler_test_between_type_alter',
        'module_handler_test2_type_alter',
        '_module_handler_test_late_type_alter',
      ],
      [
        '_module_handler_test_early_type_alter',
        '_module_handler_test_early_subtype_alter',
        '_module_handler_test_early_unaltered_alter',
        'module_handler_test_type_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test_unaltered_alter',
        'module_handler_test1_type_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test1_unaltered_alter',
        '_module_handler_test_between_type_alter',
        '_module_handler_test_between_subtype_alter',
        '_module_handler_test_between_unaltered_alter',
        'module_handler_test2_type_alter',
        'module_handler_test2_subtype_alter',
        'module_handler_test2_unaltered_alter',
        '_module_handler_test_late_type_alter',
        '_module_handler_test_late_subtype_alter',
        '_module_handler_test_late_unaltered_alter',
      ],
      [
        'module_handler_test_unaltered_alter',
        'module_handler_test_subtype_alter',
        'module_handler_test1_unaltered_alter',
        'module_handler_test1_subtype_alter',
        'module_handler_test2_unaltered_alter',
        'module_handler_test2_subtype_alter',
      ],
    ];
    return $datasets;
  }

  /**
   * Tests that write cache calls through to cache library correctly.
   *
   * @covers ::writeCache
   */
  public function testWriteCache() {
    $module_handler = $this->getModuleHandler();
    $this->cacheBackend
      ->expects($this->exactly(2))
      ->method('get')
      ->willReturn(NULL);
    $this->cacheBackend
      ->expects($this->exactly(2))
      ->method('set')
      ->with($this->logicalOr('module_implements', 'hook_info'));
    $module_handler->invokeAllWith('hook', function (callable $hook, string $module) {});
    $module_handler->writeCache();
  }

  /**
   * Tests hook_hook_info() fetching through getHookInfo().
   *
   * @covers ::getHookInfo
   * @covers ::buildHookInfo
   */
  public function testGetHookInfo() {
    $module_handler = $this->getModuleHandler();
    // Set up some synthetic results.
    $this->cacheBackend
      ->expects($this->exactly(2))
      ->method('get')
      ->will($this->onConsecutiveCalls(
        NULL,
        (object) ['data' => ['hook_foo' => ['group' => 'hook']]]
      ));

    // Results from building from mocked environment.
    $this->assertEquals([
      'hook' => ['group' => 'hook'],
    ], $module_handler->getHookInfo());

    // Reset local cache so we get our synthetic result from the cache handler.
    $module_handler->resetImplementations();
    $this->assertEquals([
      'hook_foo' => ['group' => 'hook'],
    ], $module_handler->getHookInfo());
  }

  /**
   * Tests internal implementation cache reset.
   *
   * @covers ::resetImplementations
   */
  public function testResetImplementations() {
    $module_handler = $this->getModuleHandler();
    // Prime caches
    $module_handler->invokeAllWith('hook', function (callable $hook, string $module) {});
    $module_handler->getHookInfo();

    // Reset all caches internal and external.
    $this->cacheBackend
      ->expects($this->once())
      ->method('delete')
      ->with('hook_info');
    $this->cacheBackend
      ->expects($this->exactly(2))
      ->method('set')
      // reset sets module_implements to array() and getHookInfo later
      // populates hook_info.
      ->with($this->logicalOr('module_implements', 'hook_info'));
    $module_handler->resetImplementations();

    // Request implementation and ensure hook_info and module_implements skip
    // local caches.
    $this->cacheBackend
      ->expects($this->exactly(2))
      ->method('get')
      ->with($this->logicalOr('module_implements', 'hook_info'));
    $module_handler->invokeAllWith('hook', function (callable $hook, string $module) {});
  }

  /**
   * @covers ::getModuleDirectories
   */
  public function testGetModuleDirectories() {
    $module_handler = $this->getModuleHandler();
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
      implode("\n", $expected) . "\n",
      implode("\n", $actual) . "\n",
      $message,
    );
    // Make sure that array keys are as expected.
    $this->assertSame($expected, $actual);
  }

}
