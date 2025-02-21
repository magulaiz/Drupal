<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Database;

/**
 * Tests protection against Gadget Chains.
 *
 * @group Database
 * @group legacy
 */
class StatementPrefetchGadgetChainTest extends DatabaseTestBase {

  /**
   * Test protection against a DependencySerializationTrait-based Gadget Chain.
   */
  public function testDependencySerializationTraitGadgetChain(): void {
    $payload = 'O:15:"Drupal\Core\Url":1:{s:11:"_serviceIds";O:38:"Drupal\Core\Database\StatementPrefetch":3:{s:10:"currentRow";a:0:{}s:10:"fetchStyle";i:8;s:12:"fetchOptions";a:2:{s:5:"class";s:10:"FakeRecord";s:16:"constructor_args";a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}}}}';
    try {
      // Do not assign the return value of unserialize as we want the objects
      // to be destructed immediately.
      unserialize($payload);
      $this->fail('Expected TypeError to be thrown.');
    }
    catch (\TypeError $e) {
      $this->assertEquals('TypeError', get_class($e), get_class($e) . ' thrown when unserializing payload.');
    }
  }

  /**
   * Test protection against an Update Query-based Gadget Chain.
   */
  public function testUpdateQueryGadgetChain(): void {
    $payload = 'O:33:"Drupal\Core\Database\Query\Update":2:{s:9:"condition";O:36:"Drupal\Core\Database\Query\Condition":0:{}s:16:"expressionFields";O:38:"Drupal\Core\Database\StatementPrefetch":3:{s:10:"currentRow";a:0:{}s:10:"fetchStyle";i:8;s:12:"fetchOptions";a:2:{s:5:"class";s:10:"FakeRecord";s:16:"constructor_args";a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}}}}';
    try {
      // In this case we can assign the return value; casting it to a string
      // invokes the relevant __toString magic method.
      $result = (string) unserialize($payload);
      $this->fail('Expected UnexpectedValueException to be thrown.');
    }
    catch (\Exception $e) {
      $this->assertEquals('UnexpectedValueException', get_class($e), get_class($e) . ' thrown when unserializing payload.');
    }
  }

}
