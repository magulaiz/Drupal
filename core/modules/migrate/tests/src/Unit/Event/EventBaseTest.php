<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\Event\EventBase;
use Drupal\Tests\UnitTestCase;

/**
 * @group migrate
 */
#[CoversClass(\Drupal\migrate\Event\EventBase::class)]
class EventBaseTest extends UnitTestCase {

  /**
   * Tests getMigration method.
   */
  public function testGetMigration() {
    $migration = $this->prophesize('\Drupal\migrate\Plugin\MigrationInterface')->reveal();
    $message_service = $this->prophesize('\Drupal\migrate\MigrateMessageInterface')->reveal();
    $event = new EventBase($migration, $message_service);
    $this->assertSame($migration, $event->getMigration());
  }

  /**
   * Tests logging a message.
   */
  public function testLogMessage() {
    $migration = $this->prophesize('\Drupal\migrate\Plugin\MigrationInterface')->reveal();
    $message_service = $this->prophesize('\Drupal\migrate\MigrateMessageInterface');
    $event = new EventBase($migration, $message_service->reveal());
    // Assert that the intended calls to the services happen.
    $message_service->display('status message', 'status')->shouldBeCalledTimes(1);
    $event->logMessage('status message');
    $message_service->display('warning message', 'warning')->shouldBeCalledTimes(1);
    $event->logMessage('warning message', 'warning');
  }

}
