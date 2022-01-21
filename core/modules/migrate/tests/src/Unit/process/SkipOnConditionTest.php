<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\migrate\process\SkipOnCondition;

/**
 * Tests the skip on condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\SkipOnCondition
 */
class SkipOnConditionTest extends MigrateProcessTestCase {

  public function testConfigurationValidation() {
    $configuration = [];
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "operation" must be set.');
    (new SkipOnCondition($configuration, 'skip_on_condition', []))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');

    $configuration = ['operation' => 'hello'];
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "hello" operation is not supported.');
    (new SkipOnCondition($configuration, 'skip_on_condition', []))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');

    $configuration = ['operation' => '<'];
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "<" operation requires "compared_value" to be set.');
    (new SkipOnCondition($configuration, 'skip_on_condition', []))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * @covers ::process
   * @dataProvider providerTestEmptyOperation
   */
  public function testEmptyOperation($value, $negate) {
    $configuration = [
      'method' => 'process',
      'operation' => 'empty',
      'negate' => $negate,
    ];
    $this->expectException(MigrateSkipProcessException::class);

    (new SkipOnCondition($configuration, 'skip_on_condition', []))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * Data provider for ::testEmptyOperation().
   */
  public function providerTestEmptyOperation() {
    return [
      'string' => ['', FALSE],
      'array' => [[], FALSE],
      'array_negate' => [['banana', 'apple'], TRUE],
      'bool_negate' => [TRUE, TRUE],
      'null' => [NULL, FALSE],
    ];
  }

}
