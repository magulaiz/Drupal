<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Hook;

use Drupal\hk_a_test\Hook\AAlterHooks;
use Drupal\hk_a_test\Hook\AFormAlterHooks;
use Drupal\hk_a_test\Hook\AHooks;
use Drupal\hk_a_test\Hook\ModuleImplementsAlter;
use Drupal\hk_b_test\Hook\BAlterHooks;
use Drupal\hk_b_test\Hook\BFormAlterHooks;
use Drupal\hk_b_test\Hook\BHooks;
use Drupal\hk_c_test\Hook\CAlterHooks;
use Drupal\hk_c_test\Hook\CFormAlterHooks;
use Drupal\hk_c_test\Hook\CHooks;
use Drupal\hk_d_test\Hook\DAlterHooks;
use Drupal\hk_d_test\Hook\DHooks;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group Hook
 */
class HookOrderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'hk_a_test',
    'hk_b_test',
    'hk_c_test',
    'hk_d_test',
  ];

  public function testProceduralAlterOrder(): void {
    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_b_test_procedural_alter',
      'hk_c_test_procedural_alter',
    ], 'procedural');

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_subtype_alter',
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_subtype_alter',
    ], 'procedural_subtype');

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_a_test_procedural_subtype_alter',
      'hk_b_test_procedural_alter',
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_alter',
      'hk_c_test_procedural_subtype_alter',
    ], ['procedural', 'procedural_subtype']);

    // Test with module B moved to the end.
    ModuleImplementsAlter::set(
      function (array &$implementations, string $hook): void {
        if (!in_array($hook, ['procedural_alter', 'procedural_subtype_alter'])) {
          return;
        }
        $this->assertSameCallList([
          'hk_a_test',
          'hk_b_test',
          'hk_c_test',
        ], array_keys($implementations));
        // Move B to the end, no matter which hook.
        $group = $implementations['hk_b_test'];
        unset($implementations['hk_b_test']);
        $implementations['hk_b_test'] = $group;
      },
    );
    \Drupal::service('kernel')->rebuildContainer();

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_c_test_procedural_alter',
      // The implementation of B has been moved.
      'hk_b_test_procedural_alter',
    ], 'procedural', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      // The implementation of B is back to its original position.
      'hk_b_test_procedural_alter',
      'hk_c_test_procedural_alter',
    ], ['x', 'procedural']);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_subtype_alter',
      'hk_c_test_procedural_subtype_alter',
      // The implementation of B has been moved.
      'hk_b_test_procedural_subtype_alter',
    ], 'procedural_subtype', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_subtype_alter',
      // The implementation of B is back to its original position.
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_subtype_alter',
    ], ['x', 'procedural_subtype'], prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_a_test_procedural_subtype_alter',
      'hk_c_test_procedural_alter',
      'hk_c_test_procedural_subtype_alter',
      'hk_b_test_procedural_alter',
      'hk_b_test_procedural_subtype_alter',
    ], ['procedural', 'procedural_subtype'], prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_a_test_procedural_subtype_alter',
      // The implementations of B are back to their original position.
      'hk_b_test_procedural_alter',
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_alter',
      'hk_c_test_procedural_subtype_alter',
    ], ['x', 'procedural', 'procedural_subtype'], prepend_unknown_type: FALSE);

    // Test with module B moved to the end for the main hook.
    ModuleImplementsAlter::set(
      function (array &$implementations, string $hook): void {
        if (!in_array($hook, ['procedural_alter', 'procedural_subtype_alter'])) {
          return;
        }
        $this->assertSameCallList([
          'hk_a_test',
          'hk_b_test',
          'hk_c_test',
        ], array_keys($implementations));
        if ($hook !== 'procedural_alter') {
          return;
        }
        // Move B to the end, no matter which hook.
        $group = $implementations['hk_b_test'];
        unset($implementations['hk_b_test']);
        $implementations['hk_b_test'] = $group;
      },
    );
    \Drupal::service('kernel')->rebuildContainer();

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_c_test_procedural_alter',
      // The main hook has B last.
      'hk_b_test_procedural_alter',
    ], 'procedural', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      // The main hook has B in its original position.
      'hk_b_test_procedural_alter',
      'hk_c_test_procedural_alter',
    ], ['x', 'procedural'], prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_subtype_alter',
      // The subtype hook has B in its original place.
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_subtype_alter',
    ], 'procedural_subtype');

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_a_test_procedural_subtype_alter',
      'hk_c_test_procedural_alter',
      'hk_c_test_procedural_subtype_alter',
      // The mixed hook has B last.
      'hk_b_test_procedural_alter',
      'hk_b_test_procedural_subtype_alter',
    ], ['procedural', 'procedural_subtype'], prepend_unknown_type: FALSE);

    // Test with module B moved to the end for the subtype hook.
    ModuleImplementsAlter::set(
      function (array &$implementations, string $hook): void {
        if (!in_array($hook, ['procedural_alter', 'procedural_subtype_alter'])) {
          return;
        }
        $this->assertSameCallList([
          'hk_a_test',
          'hk_b_test',
          'hk_c_test',
        ], array_keys($implementations));
        if ($hook !== 'procedural_subtype_alter') {
          return;
        }
        // Move B to the end, no matter which hook.
        $group = $implementations['hk_b_test'];
        unset($implementations['hk_b_test']);
        $implementations['hk_b_test'] = $group;
      },
    );
    \Drupal::service('kernel')->rebuildContainer();

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      // The main hook has B in its original place.
      'hk_b_test_procedural_alter',
      'hk_c_test_procedural_alter',
    ], 'procedural');

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_subtype_alter',
      'hk_c_test_procedural_subtype_alter',
      // The subtype hook has B last.
      'hk_b_test_procedural_subtype_alter',
    ], 'procedural_subtype', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      'hk_a_test_procedural_alter',
      'hk_a_test_procedural_subtype_alter',
      // The mixed hook has B in its original place.
      'hk_b_test_procedural_alter',
      'hk_b_test_procedural_subtype_alter',
      'hk_c_test_procedural_alter',
      'hk_c_test_procedural_subtype_alter',
    ], ['procedural', 'procedural_subtype']);
  }

  public function testAlterOrder(): void {
    $this->assertAlterCallOrder([
      CAlterHooks::class . '::testAlter',
      BAlterHooks::class . '::testAlterAfterCExtra',
      AAlterHooks::class . '::testAlterAfterC',
      DAlterHooks::class . '::testAlter',
    ], 'test');

    $this->assertAlterCallOrder([
      AAlterHooks::class . '::testSubtypeAlter',
      BAlterHooks::class . '::testSubtypeAlter',
      CAlterHooks::class . '::testSubtypeAlter',
      DAlterHooks::class . '::testSubtypeAlter',
    ], 'test_subtype', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      // The implementation from 'D' is gone.
      AAlterHooks::class . '::testSubtypeAlter',
      BAlterHooks::class . '::testSubtypeAlter',
      CAlterHooks::class . '::testAlter',
      CAlterHooks::class . '::testSubtypeAlter',
      BAlterHooks::class . '::testAlterAfterCExtra',
      AAlterHooks::class . '::testAlterAfterC',
      DAlterHooks::class . '::testAlter',
      DAlterHooks::class . '::testSubtypeAlter',
    ], ['test', 'test_subtype']);

    $this->disableModules(['hk_b_test']);

    $this->assertAlterCallOrder([
      CAlterHooks::class . '::testAlter',
      AAlterHooks::class . '::testAlterAfterC',
      DAlterHooks::class . '::testAlter',
    ], 'test', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      AAlterHooks::class . '::testSubtypeAlter',
      CAlterHooks::class . '::testSubtypeAlter',
      DAlterHooks::class . '::testSubtypeAlter',
    ], 'test_subtype', prepend_unknown_type: FALSE);

    $this->assertAlterCallOrder([
      AAlterHooks::class . '::testSubtypeAlter',
      CAlterHooks::class . '::testAlter',
      CAlterHooks::class . '::testSubtypeAlter',
      AAlterHooks::class . '::testAlterAfterC',
      DAlterHooks::class . '::testAlter',
      DAlterHooks::class . '::testSubtypeAlter',
    ], ['test', 'test_subtype'], prepend_unknown_type: FALSE);
  }

  public function testFormAlterOrder(): void {
    $this->assertSameCallList([
      AFormAlterHooks::class . '::formAlter',
      BFormAlterHooks::class . '::formAlter',
      AFormAlterHooks::class . '::formAlterAfterBExtra',
      AFormAlterHooks::class . '::formAlterAfterB',
      CFormAlterHooks::class . '::formAlter',
    ], $this->alter('form')['#calls'] ?? NULL);

    $this->assertSameCallList([
      AFormAlterHooks::class . '::formAlter',
      AFormAlterHooks::class . '::myFormAlter',
      BFormAlterHooks::class . '::formAlter',
      BFormAlterHooks::class . '::myFormAlter',
      AFormAlterHooks::class . '::myFormAlterAfterBExtra',
      AFormAlterHooks::class . '::myFormAlterAfterB',
      AFormAlterHooks::class . '::formAlterAfterBExtra',
      AFormAlterHooks::class . '::formAlterAfterB',
      CFormAlterHooks::class . '::formAlter',
      CFormAlterHooks::class . '::myFormAlter',
    ], $this->alter(['form', 'form_myform'])['#calls'] ?? NULL);
  }

  public function testHookOrder(): void {
    $this->assertSameCallList(
      [
        CHooks::class . '::testHookReOrderFirst',
        CHooks::class . '::testHookFirst',
        CHooks::class . '::testHook',
        'hk_c_test_testhook',
        AHooks::class . '::testHookFirst',
        'hk_a_test_testhook',
        AHooks::class . '::testHook',
        AHooks::class . '::testHookAfterB',
        AHooks::class . '::testHookLast',
        'hk_b_test_testhook',
        BHooks::class . '::testHook',
        'hk_d_test_testhook',
        DHooks::class . '::testHook',
      ],
      \Drupal::moduleHandler()->invokeAll('testhook'),
    );
  }

  /**
   * Asserts the call order from an alter call.
   *
   * Also asserts additional $type argument values that are meant to produce the
   * same result.
   *
   * @param list<string> $expected
   *   Expected call list, as strings from __METHOD__ or __FUNCTION__.
   * @param string|list<string> $type
   *   First argument to pass to ->alter().
   * @param list<string|list<string>>|null $equivalent_types
   *   Alternative values for $type that are meant to produce the same result.
   *   If NULL, alternative value will be generated by appending and/
   *   prepending "unknown" types, that is, types with no implementations.
   * @param bool $prepend_unknown_type
   *   If TRUE, or if NULL and $equivalent_types is NULL, additional equivalent
   *   types will be generated where an unknown type is prepended.
   */
  protected function assertAlterCallOrder(array $expected, string|array $type, array|null $equivalent_types = NULL, ?bool $prepend_unknown_type = NULL): void {
    if ($equivalent_types === NULL) {
      $equivalent_type = [];
      foreach ((array) $type as $i => $type_i) {
        $equivalent_type[] = $type_i;
        $equivalent_type[] = 'x_' . $i;
      }
      $equivalent_types = [$equivalent_type];
      $prepend_unknown_type ??= TRUE;
    }
    if ($prepend_unknown_type ?? FALSE) {
      foreach ([(array) $type, ...$equivalent_types] as $type_i) {
        $equivalent_types[] = ['x', ...$type_i];
      }
    }
    foreach ([$type, ...$equivalent_types] as $i => $type_i) {
      $this->assertSameCallList(
        $expected,
        $this->alter($type_i),
        $i . ': ' . json_encode($type_i),
      );
    }
  }

  /**
   * Invokes ModuleHandler->alter() and returns the altered array.
   *
   * @param string|list<string> $type
   *   Alter type or list of alter types.
   *
   * @return array
   *   The altered array.
   */
  protected function alter(string|array $type): array {
    $data = [];
    \Drupal::moduleHandler()->alter($type, $data);
    return $data;
  }

  /**
   * Asserts that two lists of call strings are the same.
   *
   * It is meant for strings produced with __FUNCTION__ or __METHOD__.
   *
   * The assertion fails exactly when a regular ->assertSame() would fail, but
   * it provides a more useful output on failure.
   *
   * @param list<string> $expected
   *   Expected list of strings.
   * @param list<string> $actual
   *   Actual list of strings.
   * @param string $message
   *   Message to pass to ->assertSame().
   */
  protected function assertSameCallList(array $expected, array $actual, string $message = '') {
    // Format without the numeric array keys, but in a way that can be easily
    // copied into the test.
    $format = function (array $strings): string {
      if (!$strings) {
        return '[]';
      }
      $parts = array_map(
        function (string $call_string) {
          if (preg_match('@^(\w+\\\\)*(\w+)::(\w+)@', $call_string, $matches)) {
            [,, $class_shortname, $method] = $matches;
            return $class_shortname . '::class . ' . var_export('::' . $method, TRUE);
          }
          return var_export($call_string, TRUE);
        },
        $strings,
      );
      return "[\n  " . implode(",\n  ", $parts) . ",\n]";
    };
    $this->assertSame(
      $format($expected),
      $format($actual),
      $message,
    );
    // Finally, assert that array keys and the full class names are really the
    // same, in a way that provides useful output on failure.
    $this->assertSame($expected, $actual, $message);
  }

}
