<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Hook;

use Drupal\hk_a_test\Hook\AHooks;
use Drupal\hk_b_test\Hook\BHooks;
use Drupal\hk_c_test\Hook\CHooks;
use Drupal\hk_d_test\Hook\DHooks;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

/**
 * @group Hook
 */
#[IgnoreDeprecations]
class HookOrderTest extends KernelTestBase {

  use HookOrderTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'hk_a_test',
    'hk_b_test',
    'hk_c_test',
    'hk_d_test',
  ];

  public function testHookOrder(): void {
    $this->assertSameCallList(
      [
        CHooks::class . '::testHookReOrderFirst',
        CHooks::class . '::testHookFirst',
        AHooks::class . '::testHookFirst',
        'hk_a_test_test_hook',
        AHooks::class . '::testHook',
        'hk_b_test_test_hook',
        BHooks::class . '::testHook',
        AHooks::class . '::testHookAfterB',
        'hk_c_test_test_hook',
        CHooks::class . '::testHook',
        'hk_d_test_test_hook',
        DHooks::class . '::testHook',
        AHooks::class . '::testHookLast',
      ],
      \Drupal::moduleHandler()->invokeAll('test_hook'),
    );
  }

  /**
   * Tests hook order when each module has either oop or procedural listeners.
   *
   * This would detect a possible mistake where we would first collect modules
   * from all procedural and then from all oop implementations, without fixing
   * the order.
   */
  public function testSparseHookOrder(): void {
    $this->assertSameCallList(
      [
        // OOP and procedural listeners are correctly intermixed by module
        // order.
        'hk_a_test_sparse_test_hook',
        BHooks::class . '::sparseTestHook',
        'hk_c_test_sparse_test_hook',
        DHooks::class . '::sparseTestHook',
      ],
      \Drupal::moduleHandler()->invokeAll('sparse_test_hook'),
    );
  }

}
