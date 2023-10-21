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
    $result = $this->connection->query("SELECT CONCAT_WS('-', [name], [age]) FROM {test} WHERE [age] = :age", [
      ':age' => 25,
    ]);
    $this->assertSame('name-John-age-25', $result->fetchField());
  }

}
