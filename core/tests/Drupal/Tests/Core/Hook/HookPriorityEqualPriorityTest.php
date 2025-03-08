<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Hook;

use Drupal\Core\Hook\Order;
use Drupal\Core\Hook\OrderAfter;
use Drupal\Core\Hook\OrderBefore;

/**
 * @coversDefaultClass \Drupal\Core\Hook\HookPriority
 *
 * @group Hook
 */
class HookPriorityEqualPriorityTest extends HookPriorityTestBase {

  protected function setUp(): void {
    parent::setUp();
    // The priority of "a", "b", "c" are the same, the order is undefined.
    $this->setUpContainer(FALSE);
    $this->assertSame($this->getPriority('a'), $this->getPriority('b'));
    $this->assertSame($this->getPriority('b'), $this->getPriority('c'));
  }

  public function testFirst(): void {
    // "c" was first, make "a" the first.
    $this->doPriorityChange('a', Order::First);
    $this->assertGreaterThan($this->getPriority('c'), $this->getPriority('a'));
    $this->assertGreaterThan($this->getPriority('b'), $this->getPriority('a'));
    // Nothing else can be asserted: by setting the same priority, the setup
    // had undefined order and so the services not included in the helper call
    // can be in any order.
  }

  public function testLast(): void {
    // "c" was first, make it the last.
    $this->doPriorityChange('c', Order::Last);
    $this->assertGreaterThan($this->getPriority('c'), $this->getPriority('a'));
    $this->assertGreaterThan($this->getPriority('c'), $this->getPriority('b'));
    // Nothing else can be asserted: by setting the same priority, the setup
    // had undefined order and so the services not included in the helper call
    // can be in any order.
  }

  public function testBefore(): void {
    // "a" was last, move it before "b".
    $this->doPriorityChange('a', OrderBefore::class, 'b');
    $this->assertGreaterThan($this->getPriority('b'), $this->getPriority('a'));
    // Nothing else can be asserted: by setting the same priority, the setup
    // had undefined order and so the services not included in the helper call
    // can be in any order.
  }

  public function testAfter(): void {
    // "c" was first, move it after "b".
    $this->doPriorityChange('c', OrderAfter::class, 'b');
    $this->assertGreaterThan($this->getPriority('c'), $this->getPriority('b'));
    // Nothing else can be asserted: by setting the same priority, the setup
    // had undefined order and so the services not included in the helper call
    // can be in any order.
  }

}
