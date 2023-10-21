<?php

namespace Drupal\Tests\mysqli\Kernel\mysqli;

use Drupal\KernelTests\Core\Database\DriverSpecificSyntaxTestBase;

/**
 * Tests MySql syntax interpretation.
 *
 * @group Database
 */
class SyntaxTest extends DriverSpecificSyntaxTestBase {

  /**
   * Tests string concatenation with separator, with field values.
   */
  public function testConcatWsFields() {
    $result = $this->connection->query("SELECT CONCAT_WS('-', :a1, [name], :a2, [age]) FROM {test} WHERE [age] = :age COLLATE utf8mb4_general_ci", [
      ':a1' => 'name',
      ':a2' => 'age',
      ':age' => 25,
    ]);
    $this->assertSame('name-John-age-25', $result->fetchField());
  }

}
