<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\migrate\MigrateStub;
use Drupal\migrate\Plugin\migrate\source\EmbeddedDataSource;
use Drupal\migrate\Plugin\MigrateDestinationInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;

/**
 * Tests the migrate stub service.
 *
 * @group migrate
 *
 * @coversDefaultClass \Drupal\migrate\MigrateStub
 */
class MigrateStubTest extends UnitTestCase {

  /**
   * The plugin manager prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $migrationPluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrationPluginManager = $this->prophesize(MigrationPluginManagerInterface::class);
  }

  /**
   * Tests stubbing.
   *
   * @covers ::createStub
   */
  public function testCreateStub(): void {
    $ids = ['id' => ['type' => 'integer']];
    $row_source_1_missing = new Row(['id' => 1], $ids, TRUE);
    $row_source_2 = new Row(['id' => 2], $ids, TRUE);
    $destination_2 = ['id' => 2];
    $destination_plugin = $this->prophesize(MigrateDestinationInterface::class);
    $destination_plugin->import($row_source_1_missing)->willReturn(['id' => 2]);
    $destination_plugin->import($row_source_2)->willReturn($destination_2);
    $id_map = $this->prophesize(MigrateIdMapInterface::class);

    $migration = $this->prophesize(MigrationInterface::class);
    $migration->id()->willReturn('test_migration');
    $migration->getIdMap()->willReturn($id_map->reveal());
    $migration->getDestinationPlugin(TRUE)->willReturn($destination_plugin->reveal());
    $migration->getProcessPlugins([])->willReturn([]);
    $migration->getProcess()->willReturn([]);
    $migration->getSourceConfiguration()->willReturn([]);
    $source_plugin = new EmbeddedDataSource([
      'data_rows' => [
        $row_source_2->getSource(),
      ],
      'ids' => $ids,
    ], 'embedded_data', [], $migration->reveal());
    $migration->getSourcePlugin()->willReturn($source_plugin);

    // The source plugin's prepareRow method uses a module handler fetched from
    // the service container.
    // @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase::prepareRow()
    // @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase::getModuleHandler()
    $module_handler = $this->createMock(ModuleHandlerInterface::class);
    $module_handler
      ->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);
    $container = new ContainerBuilder();
    $container->set('module_handler', $module_handler);
    \Drupal::setContainer($container);
    $this->migrationPluginManager->createInstances(['test_migration'])->willReturn([$migration->reveal()]);

    $stub = new MigrateStub($this->migrationPluginManager->reveal());

    $this->assertSame(['id' => 2], $stub->createStub('test_migration', ['id' => 1], []));
    // If MigrateStub is asked to create only valid stubs, then with the
    // incoming "['id' => 1]" source IDs array shouldn't create a stub.
    $this->assertFalse($stub->createStub('test_migration', $row_source_1_missing->getSource(), [], NULL, TRUE));
    $this->assertSame($destination_2, $stub->createStub('test_migration', $row_source_2->getSource(), [], NULL, TRUE));
  }

  /**
   * Tests that an error is logged if the plugin manager throws an exception.
   */
  public function testExceptionOnPluginNotFound(): void {
    $this->migrationPluginManager->createInstances(['test_migration'])->willReturn([]);
    $this->expectException(PluginNotFoundException::class);
    $this->expectExceptionMessage("Plugin ID 'test_migration' was not found.");
    $stub = new MigrateStub($this->migrationPluginManager->reveal());
    $stub->createStub('test_migration', [1]);
  }

  /**
   * Tests that an error is logged on derived migrations.
   */
  public function testExceptionOnDerivedMigration(): void {
    $this->migrationPluginManager->createInstances(['test_migration'])->willReturn([
      'test_migration:d1' => $this->prophesize(MigrationInterface::class)->reveal(),
      'test_migration:d2' => $this->prophesize(MigrationInterface::class)->reveal(),
    ]);
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Cannot stub derivable migration "test_migration".  You must specify the id of a specific derivative to stub.');
    $stub = new MigrateStub($this->migrationPluginManager->reveal());
    $stub->createStub('test_migration', [1]);
  }

}
