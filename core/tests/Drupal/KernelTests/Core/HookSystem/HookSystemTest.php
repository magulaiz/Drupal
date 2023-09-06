<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Core\HookSystem;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\KernelTests\Core\HookSystem\Attribute\ExtraModules;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the hook system.
 *
 * @group HookSystem
 * @coversDefaultClass \Drupal\Core\Extension\ModuleHandler
 */
class HookSystemTest extends KernelTestBase {

  protected const MODULES_PATH = 'core/tests/modules';

  protected const TEST_MODULE_PATH = self::MODULES_PATH . '/hooks_test';

  /**
   * Modules to enable.
   *
   * @var list<string>
   */
  protected static $modules = ['hooks_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    Extension::$skipAssertFileExists = TRUE;
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  protected function getModulesToEnable(): array {
    $modules = parent::getModulesToEnable();
    $extra_module_names = $this->getExtraModuleNames();
    return array_unique(array_merge($modules, $extra_module_names));
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailableExtensions(string $type): array {
    $extensions = parent::getAvailableExtensions($type);
    if ($type === 'module') {
      foreach ($this->getModulesToEnable() as $module) {
        if (!isset($extensions[$module])) {
          $extensions[$module] = new Extension(
            $this->root,
            'module',
            // Set a fake module path.
            'tests/modules/' . $module . '/' . $module . '.info.yml',
          );
        }
      }
    }
    return $extensions;
  }

  /**
   * Gets additional module names to enable.
   *
   * @return list<string>
   *   Module names.
   */
  private function getExtraModuleNames(): array {
    $rm = new \ReflectionMethod(static::class, $this->getName(FALSE));
    $extra_modules = [];
    foreach ($rm->getAttributes(ExtraModules::class) as $attribute) {
      /** @var \Drupal\KernelTests\Core\HookSystem\Attribute\ExtraModules $attribute_instance */
      $attribute_instance = $attribute->newInstance();
      foreach ($attribute_instance->modules as $module) {
        $extra_modules[] = $module;
      }
    }
    foreach ($rm->getParameters() as $parameter) {
      if ($parameter->getName() === 'modules' && $parameter->getType()->__toString() === 'array') {
        foreach ($this->getProvidedData()[0] as $module) {
          $this->assertIsString($module);
          $extra_modules[] = $module;
        }
      }
    }
    return $extra_modules;
  }

  /**
   * Tests single-module hook invocation.
   */
  public function testInvoke(): void {
    $module_handler = $this->getModuleHandler();
    $this->assertSame(
      'hooks_test_testhook(arg)',
      $module_handler->invoke('hooks_test', 'testhook', ['arg']),
      'Module is installed, implementation exists.',
    );
    $this->assertNull(
      $module_handler->invoke('hooks_test', 'testhook1', ['arg']),
      'Module is installed, implementation not loaded.',
    );
    $this->assertNull(
      $module_handler->invoke('hooks_test_new', 'testhook', ['arg']),
      'Module is not installed, implementation not loaded.',
    );

    // Files like *.install can be included _after_ initial discovery.
    require_once __DIR__ . '/includes/functions.inc';

    // Implementations from the included file now work.
    $this->assertSame(
      'hooks_test_testhook1(arg)',
      $module_handler->invoke('hooks_test', 'testhook1', ['arg']),
      'Module is installed, implementation exists.',
    );
    $this->assertSame(
      'hooks_test_new_testhook(arg)',
      $module_handler->invoke('hooks_test_new', 'testhook', ['arg']),
      'Module is not installed, implementation exists.',
    );
  }

  /**
   * Tests single-module hook invocation with by-reference argument.
   */
  public function testInvokeByReference(): void {
    $module_handler = $this->getModuleHandler();
    $values = ['x'];
    $this->assertSame(
      'hooks_test_byref',
      $module_handler->invoke('hooks_test', 'byref', [&$values]),
    );
    $this->assertSameListsOfStrings([
      'x',
      'hooks_test_byref',
    ], $values);
  }

  /**
   * Tests implementations methods when module is enabled.
   */
  public function testImplementsHookModuleEnabled() {
    $module_handler = $this->getModuleHandler();
    $this->assertTrue(
      $module_handler->hasImplementations('testhook', 'hooks_test'),
      'Installed module implementation found.',
    );

    $module_handler->addModule('hooks_test_added', self::MODULES_PATH . '/hooks_test_added');
    $this->assertTrue(
      $module_handler->hasImplementations('testhook', 'hooks_test_added'),
      'Runtime added module with implementation in include found.',
    );

    $module_handler->addModule('hooks_test_no_hook', self::MODULES_PATH . '/hooks_test_no_hook');
    $this->assertFalse(
      $module_handler->hasImplementations('testhook', 'hooks_test_no_hook'),
      'Missing implementation not found.',
    );
  }

  /**
   * Tests hasImplementations.
   *
   * @covers ::hasImplementations
   */
  public function testHasImplementations() {
    $module_handler = $this->getModuleHandler();

    $this->assertFalse($module_handler->hasImplementations('non_existing_hook'));

    $this->assertTrue($module_handler->hasImplementations('testhook'));
  }

  /**
   * Tests invoke all.
   *
   * @covers ::invokeAll
   */
  #[ExtraModules(['hooks_test_all1', 'hooks_test_all2'])]
  public function testInvokeAll(): void {
    $module_handler = $this->getModuleHandler();

    $this->assertSame([
      'hooks_test_testhook(arg)',
      'hooks_test_all1_testhook(arg)',
      'hooks_test_all2_testhook(arg)',
    ], $module_handler->invokeAll('testhook', ['arg']));

    // Test by-reference arguments.
    $values = ['x'];
    $this->assertSame([
      'hooks_test_byref',
      'hooks_test_all1_byref',
      'hooks_test_all2_byref',
    ], $module_handler->invokeAll('byref', [&$values]));
    $this->assertSame([
      'x',
      'hooks_test_byref',
      'hooks_test_all1_byref',
      'hooks_test_all2_byref',
    ], $values);
  }

  /**
   * Tests order of implementations in ->invokeAll().
   *
   * @dataProvider providerTestHookOrder
   *
   * @param list<string> $modules
   *   Modules to enable.
   *   This is not used in the method, but it is picked up by the register
   *   method to add the modules to the container.
   * @param list<string> $expected
   *   Expected functions for ->invokeAll('custom_order').
   * @param list<string> $expected_alter
   *   Expected functions for ->alter('type').
   * @param list<string> $expected_alter_combined
   *   Expected functions for ->alter(['type', 'subtype', 'unaltered']).
   * @param list<string> $expected_alter_combined_2
   *   Expected functions for ->alter(['unaltered', 'subtype']).
   */
  public function testHookOrder(
    array $modules,
    array $expected,
    array $expected_alter,
    array $expected_alter_combined,
    array $expected_alter_combined_2,
  ): void {
    $module_handler = $this->getModuleHandler();

    require_once __DIR__ . '/includes/functions.inc';
    require_once __DIR__ . '/includes/functions.alter.inc';
    require_once __DIR__ . '/includes/functions.module_implements_alter.inc';

    $result = $module_handler->invokeAll('custom_order');
    $this->assertSameListsOfStrings(
      $expected,
      $result,
      'invokeAll(custom_order)',
    );

    $altered = [];
    $module_handler->alter('type', $altered);
    $this->assertSameListsOfStrings(
      $expected_alter,
      $altered,
      'alter(type)',
    );

    $altered = [];
    $module_handler->alter(['type', 'subtype', 'unaltered'], $altered);
    $this->assertSameListsOfStrings(
      $expected_alter_combined,
      $altered,
      'alter([type, subtype, unaltered])',
    );

    // Scenario where the main type order is not altered, but the subtype is.
    $altered = [];
    $module_handler->alter(['unaltered', 'subtype'], $altered);
    $this->assertSameListsOfStrings(
      $expected_alter_combined_2,
      $altered,
      'alter([unaltered, subtype])',
    );
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
        // Note that 'hooks_test' is already installed.
        'hooks_test1',
        'hooks_test2',
      ],
      [
        // Implementations of hook_custom_order().
        'hooks_test_custom_order',
        'hooks_test1_custom_order',
        'hooks_test2_custom_order',
      ],
      [
        // Implementations of hook_type_alter().
        'hooks_test_type_alter',
        'hooks_test1_type_alter',
        'hooks_test2_type_alter',
      ],
      [
        // Implementations for ->alter(['type', 'subtype', 'unaltered'], ..).
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
      ],
      [
        // Implementations for ->alter(['unaltered', 'subtype'], ..).
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    $datasets['swapped'] = [
      [
        // Change the order of modules.
        'hooks_test2',
        'hooks_test1',
      ],
      [
        'hooks_test_custom_order',
        'hooks_test2_custom_order',
        'hooks_test1_custom_order',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test2_type_alter',
        'hooks_test1_type_alter',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
      ],
    ];
    $datasets['last'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() that makes *_test run last.
        'hooks_test_last',
      ],
      [
        'hooks_test1_custom_order',
        'hooks_test2_custom_order',
        'hooks_test_custom_order',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test2_type_alter',
        'hooks_test_type_alter',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    $datasets['test1_last'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() that makes *_test1 run last.
        'hooks_test1_last',
      ],
      [
        'hooks_test_custom_order',
        'hooks_test2_custom_order',
        'hooks_test1_custom_order',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test2_type_alter',
        'hooks_test1_type_alter',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    $datasets['between'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() to let *_test run after *_test1.
        'hooks_test_between',
      ],
      [
        'hooks_test1_custom_order',
        'hooks_test_custom_order',
        'hooks_test2_custom_order',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test_type_alter',
        'hooks_test2_type_alter',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    $datasets['first'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() to let *_test run first.
        'hooks_test1_first',
      ],
      [
        'hooks_test1_custom_order',
        'hooks_test_custom_order',
        'hooks_test2_custom_order',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test_type_alter',
        'hooks_test2_type_alter',
      ],
      [
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    $datasets['remove'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() to remove *_test.
        'hooks_test_remove',
      ],
      [
        'hooks_test_custom_order',
        'hooks_test2_custom_order',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test2_type_alter',
      ],
      [
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    // Use hook_module_implements_alter() to insert implementations for fake
    // modules early, late and between.
    // The test covers the _current_ behavior, which might not be ideal in all
    // cases.
    $datasets['fake_extra_modules'] = [
      [
        'hooks_test1',
        'hooks_test2',
        // Add a *_module_implements_alter() to insert fake modules.
        'hooks_test_fake',
      ],
      [
        '_hooks_test_early_custom_order',
        'hooks_test_custom_order',
        'hooks_test1_custom_order',
        '_hooks_test_between_custom_order',
        'hooks_test2_custom_order',
        '_hooks_test_late_custom_order',
      ],
      [
        '_hooks_test_early_type_alter',
        'hooks_test_type_alter',
        'hooks_test1_type_alter',
        '_hooks_test_between_type_alter',
        'hooks_test2_type_alter',
        '_hooks_test_late_type_alter',
      ],
      [
        '_hooks_test_early_type_alter',
        '_hooks_test_early_subtype_alter',
        '_hooks_test_early_unaltered_alter',
        'hooks_test_type_alter',
        'hooks_test_subtype_alter',
        'hooks_test_unaltered_alter',
        'hooks_test1_type_alter',
        'hooks_test1_subtype_alter',
        'hooks_test1_unaltered_alter',
        '_hooks_test_between_type_alter',
        '_hooks_test_between_subtype_alter',
        '_hooks_test_between_unaltered_alter',
        'hooks_test2_type_alter',
        'hooks_test2_subtype_alter',
        'hooks_test2_unaltered_alter',
        '_hooks_test_late_type_alter',
        '_hooks_test_late_subtype_alter',
        '_hooks_test_late_unaltered_alter',
      ],
      [
        'hooks_test_unaltered_alter',
        'hooks_test_subtype_alter',
        'hooks_test1_unaltered_alter',
        'hooks_test1_subtype_alter',
        'hooks_test2_unaltered_alter',
        'hooks_test2_subtype_alter',
      ],
    ];
    return $datasets;
  }

  /**
   * Gets the module handler from the container.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   Module handler.
   */
  private function getModuleHandler(): ModuleHandlerInterface {
    return $this->container->get('module_handler');
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
