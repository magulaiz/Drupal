<?php

namespace Drupal\Tests\migrate\Unit\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\PluginEventSubscriber;
use Drupal\migrate\Row;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\PluginEventSubscriber
 * @group migrate
 * @legacy
 */
class PluginEventSubscriberTest extends UnitTestCase {

  const DEPRECATION_MESSAGE = 'Replace hook implementations with \Drupal\migrate\Event\MigrateEvents::PREPARE_ROW event subscribers. In order to skip the row, throw \Drupal\migrate\MigrateSkipRowException in the event subscriber. See https://www.drupal.org/node/2952459';

  /**
   * Test hookPrepareRow method and no skipping.
   *
   * @covers ::hookPrepareRow
   */
  public function testHookPrepareRowNoSkip() {
    $source = $this->createMock(SourcePluginBase::class);
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->createMock(MigrationInterface::class);
    $migration->method('id')
      ->willReturn('foo');
    $migration->method('getSourcePlugin')
      ->willReturn($source);
    $row = new Row();

    $module_handler = $this->prophesize(ModuleHandlerInterface::class);
    $module_handler->invokeAllDeprecated(static::DEPRECATION_MESSAGE, 'migrate_prepare_row', [$row, $source, $migration])
      ->willReturn([TRUE, TRUE])
      ->shouldBeCalled();
    $module_handler->invokeAllDeprecated(static::DEPRECATION_MESSAGE, 'migrate_' . $migration->id() . '_prepare_row', [$row, $source, $migration])
      ->willReturn([TRUE, TRUE]);

    $event = new MigratePreRowSaveEvent($migration, new MigrateMessage(), $row);
    $event_subscriber = new PluginEventSubscriber($module_handler->reveal());
    $event_subscriber->hookPrepareRow($event);
  }

  /**
   * Test hookPrepareRow method and migrate_prepare_row skips row.
   *
   * @covers ::hookPrepareRow
   */
  public function testHookPrepareRowSkip() {
    $source = $this->createMock(SourcePluginBase::class);
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->createMock(MigrationInterface::class);
    $migration->method('id')
      ->willReturn('foo');
    $migration->method('getSourcePlugin')
      ->willReturn($source);
    $row = new Row();

    $module_handler = $this->prophesize(ModuleHandlerInterface::class);
    $module_handler->invokeAllDeprecated(static::DEPRECATION_MESSAGE, 'migrate_prepare_row', [$row, $source, $migration])
      ->willReturn([FALSE, TRUE])
      ->shouldBeCalled();
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage('The hook migrate_prepare_row has skipped this row.');

    $event = new MigratePreRowSaveEvent($migration, new MigrateMessage(), $row);
    $event_subscriber = new PluginEventSubscriber($module_handler->reveal());
    $event_subscriber->hookPrepareRow($event);
  }

  /**
   * Test hookPrepareRow method and migration id migrate_prepare_row skips row.
   *
   * @covers ::hookPrepareRow
   */
  public function testHookPrepareRowMigrationIdSkip() {
    $source = $this->createMock(SourcePluginBase::class);
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->createMock(MigrationInterface::class);
    $migration->method('id')
      ->willReturn('foo');
    $migration->method('getSourcePlugin')
      ->willReturn($source);
    $row = new Row();

    $module_handler = $this->prophesize(ModuleHandlerInterface::class);
    $module_handler->invokeAllDeprecated(static::DEPRECATION_MESSAGE, 'migrate_prepare_row', [$row, $source, $migration])
      ->willReturn([TRUE, TRUE])
      ->shouldBeCalled();
    $module_handler->invokeAllDeprecated(static::DEPRECATION_MESSAGE, 'migrate_' . $migration->id() . '_prepare_row', [$row, $source, $migration])
      ->willReturn([FALSE, TRUE]);
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage('The hook migrate_foo_prepare_row has skipped this row.');

    $event = new MigratePreRowSaveEvent($migration, new MigrateMessage(), $row);
    $event_subscriber = new PluginEventSubscriber($module_handler->reveal());
    $event_subscriber->hookPrepareRow($event);
  }

}
