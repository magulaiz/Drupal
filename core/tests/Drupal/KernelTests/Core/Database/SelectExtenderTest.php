<?php

namespace Drupal\KernelTests\Core\Database;

use Composer\Autoload\ClassLoader;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\SelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search\SearchQuery;
use Drupal\search\ViewsSearchQuery;
use Drupal\Tests\Core\Database\Stub\StubConnection;
use Drupal\Tests\Core\Database\Stub\StubPDO;

/**
 * Tests the Select query extender classes.
 *
 * @coversDefaultClass \Drupal\Core\Database\Query\Select
 * @group Database
 */
class SelectExtenderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['database_test', 'search'];

  /**
   * Data provider for ::testExtend().
   *
   * @return array
   *   Array of arrays with the following elements:
   *   - Expected namespaced class name.
   *   - The database driver namespace.
   *   - The namespaced class name for which to extend.
   */
  public function providerExtend(): array {
    return [
      [
        'Drupal\Core\Database\Query\PagerSelectExtender',
        'Drupal\corefake\Driver\Database\corefake',
        PagerSelectExtender::class,
      ],
      [
        'Drupal\Core\Database\Query\PagerSelectExtender',
        'Drupal\corefake\Driver\Database\corefake',
        '\Drupal\Core\Database\Query\PagerSelectExtender',
      ],
      [
        'Drupal\Core\Database\Query\TableSortExtender',
        'Drupal\corefake\Driver\Database\corefake',
        TableSortExtender::class,
      ],
      [
        'Drupal\Core\Database\Query\TableSortExtender',
        'Drupal\corefake\Driver\Database\corefake',
        '\Drupal\Core\Database\Query\TableSortExtender',
      ],
      [
        'Drupal\search\SearchQuery',
        'Drupal\corefake\Driver\Database\corefake',
        SearchQuery::class,
      ],
      [
        'Drupal\search\SearchQuery',
        'Drupal\corefake\Driver\Database\corefake',
        '\Drupal\search\SearchQuery',
      ],
      [
        'Drupal\search\ViewsSearchQuery',
        'Drupal\corefake\Driver\Database\corefake',
        ViewsSearchQuery::class,
      ],
      [
        'Drupal\search\ViewsSearchQuery',
        'Drupal\corefake\Driver\Database\corefake',
        '\Drupal\search\ViewsSearchQuery',
      ],
    ];
  }

  /**
   * Data provider for ::testExtendWithLocalClasses().
   *
   * @return array
   *   Array of arrays with the following elements:
   *   - Expected namespaced class name.
   *   - The database driver namespace.
   *   - The namespaced class name for which to extend.
   */
  public function providerExtendWithLocalClasses(): array {
    return [
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\PagerSelectExtender',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        PagerSelectExtender::class,
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\PagerSelectExtender',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        '\Drupal\Core\Database\Query\PagerSelectExtender',
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\TableSortExtender',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        TableSortExtender::class,
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\TableSortExtender',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        '\Drupal\Core\Database\Query\TableSortExtender',
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\SearchQuery',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        SearchQuery::class,
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\SearchQuery',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        '\Drupal\search\SearchQuery',
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\ViewsSearchQuery',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        ViewsSearchQuery::class,
      ],
      [
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses\ViewsSearchQuery',
        'Drupal\corefake\Driver\Database\corefakeWithAllCustomClasses',
        '\Drupal\search\ViewsSearchQuery',
      ],
    ];
  }

  /**
   * @covers ::extend
   * @covers \Drupal\Core\Database\Query\SelectExtender::extend
   * @dataProvider providerExtend
   */
  public function testExtend(string $expected, string $namespace, string $extend): void {
    $additional_class_loader = new ClassLoader();
    $additional_class_loader->addPsr4("Drupal\\corefake\\Driver\\Database\\corefake\\", __DIR__ . "/../../../../../tests/fixtures/database_drivers/module/corefake/src/Driver/Database/corefake");
    $additional_class_loader->register(TRUE);

    $mock_pdo = $this->createMock(StubPDO::class);
    $connection = new StubConnection($mock_pdo, ['namespace' => $namespace]);

    // Tests the method \Drupal\Core\Database\Query\Select::extend().
    $select = $connection->select('test')->extend($extend);
    $this->assertEquals($expected, get_class($select));

    // Get an instance of the class \Drupal\Core\Database\Query\SelectExtender.
    $select_extender = $connection->select('test')->extend(SelectExtender::class);
    $this->assertEquals(SelectExtender::class, get_class($select_extender));

    // Tests the method \Drupal\Core\Database\Query\SelectExtender::extend().
    $select_extender_extended = $select_extender->extend($extend);
    $this->assertEquals($expected, get_class($select_extender_extended));
  }

  /**
   * @covers ::extend
   * @covers \Drupal\Core\Database\Query\SelectExtender::extend
   * @dataProvider providerExtendWithLocalClasses
   * @group legacy
   */
  public function testExtendWithLocalClasses(string $expected, string $namespace, string $extend): void {
    $this->expectDeprecation('Invoking Drupal\\corefake\\Driver\\Database\\corefakeWithAllCustomClasses\\%A outside of a backend overridable service is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Include the driver class in a backend overridable service instead. See https://www.drupal.org/node/3217534');
    $this->expectDeprecation('Calling %A::__construct without the %A argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. Use the relevant service to instantiate extenders. See https://www.drupal.org/node/3218001');

    $additional_class_loader = new ClassLoader();
    $additional_class_loader->addPsr4("Drupal\\corefake\\Driver\\Database\\corefakeWithAllCustomClasses\\", __DIR__ . "/../../../../../tests/fixtures/database_drivers/module/corefake/src/Driver/Database/corefakeWithAllCustomClasses");
    $additional_class_loader->register(TRUE);

    $mock_pdo = $this->createMock(StubPDO::class);
    $connection = new StubConnection($mock_pdo, ['namespace' => $namespace]);

    // Tests the method \Drupal\Core\Database\Query\Select::extend().
    $select = $connection->select('test')->extend($extend);
    $this->assertEquals($expected, get_class($select));

    // Get an instance of the class \Drupal\Core\Database\Query\SelectExtender.
    $select_extender = $connection->select('test')->extend(SelectExtender::class);
    $this->assertEquals(SelectExtender::class, get_class($select_extender));

    // Tests the method \Drupal\Core\Database\Query\SelectExtender::extend().
    $select_extender_extended = $select_extender->extend($extend);
    $this->assertEquals($expected, get_class($select_extender_extended));
  }

}
