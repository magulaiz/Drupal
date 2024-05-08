<?php

declare(strict_types=1);

namespace Drupal\Tests\pgsql\Kernel\pgsql;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\KernelTests\Core\Database\DriverSpecificKernelTestBase;

/**
 *
 * @group KernelTests
 * @group Database
 */
#[CoversClass(\Drupal\KernelTests\KernelTestBase::class)]
class KernelTestBaseTest extends DriverSpecificKernelTestBase {

  public function testSetUp() {
    // Ensure that the database tasks have been run during set up.
    $this->assertSame('on', $this->connection->query("SHOW standard_conforming_strings")->fetchField());
    $this->assertSame('escape', $this->connection->query("SHOW bytea_output")->fetchField());
  }

}
