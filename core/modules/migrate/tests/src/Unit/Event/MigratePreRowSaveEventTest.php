<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\Event\MigratePreRowSaveEvent;

/**
 * @group migrate
 */
#[CoversClass(\Drupal\migrate\Event\MigratePreRowSaveEvent::class)]
class MigratePreRowSaveEventTest extends EventBaseTest {

  /**
   * Tests getRow method.
   */
  public function testGetRow() {
    $migration = $this->prophesize('\Drupal\migrate\Plugin\MigrationInterface')->reveal();
    $message_service = $this->prophesize('\Drupal\migrate\MigrateMessageInterface')->reveal();
    $row = $this->prophesize('\Drupal\migrate\Row')->reveal();
    $event = new MigratePreRowSaveEvent($migration, $message_service, $row);
    $this->assertSame($row, $event->getRow());
  }

}
