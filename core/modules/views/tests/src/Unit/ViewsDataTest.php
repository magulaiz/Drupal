<?php

namespace Drupal\Tests\views\Kernel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Cache\NullBackend;
use Drupal\views\ViewsData;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\views\ViewsData
 * @group views
 */
class ViewsDataTest extends ViewsKernelTestBase {

  /**
   * The tested views data class.
   *
   * @var \Drupal\views\ViewsData
   */
  protected $viewsData;

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);
    $this->disableModules(['user']);
    $this->viewsData = new ViewsData($this->container->get('cache.default'), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));
  }

  /**
   * Returns the views data definition.
   */
  protected function viewsData() {
    $data = parent::viewsData();

    // Tweak the views data to have a base for testing.
    unset($data['views_test_data']['id']['field']);
    unset($data['views_test_data']['name']['argument']);
    unset($data['views_test_data']['age']['filter']);
    unset($data['views_test_data']['job']['sort']);
    $data['views_test_data']['created']['area']['id'] = 'text';
    $data['views_test_data']['age']['area']['id'] = 'text';
    $data['views_test_data']['age']['area']['sub_type'] = 'header';
    $data['views_test_data']['job']['area']['id'] = 'text';
    $data['views_test_data']['job']['area']['sub_type'] = ['header', 'footer'];

    // Duplicate the example views test data for different weight, different
    // title and matching data.
    $data['views_test_data_2'] = $data['views_test_data'];
    $data['views_test_data_2']['table']['base']['weight'] = 50;

    $data['views_test_data_3'] = $data['views_test_data'];
    $data['views_test_data_3']['table']['base']['weight'] = -50;

    $data['views_test_data_4'] = $data['views_test_data'];
    $data['views_test_data_4']['table']['base']['title'] = 'A different title';

    $data['views_test_data_5'] = $data['views_test_data'];
    $data['views_test_data_5']['table']['base']['title'] = 'Z different title';

    $data['views_test_data_6'] = $data['views_test_data'];

    return $data;
  }

  /**
   * Returns the views data definition with the provider key.
   *
   * @return array
   *
   * @see static::viewsData()
   */
  protected function viewsDataWithProvider() {
    $views_data = static::viewsData();
    foreach (array_keys($views_data) as $table) {
      $views_data[$table]['table']['provider'] = 'views_test_data';
    }
    return $views_data;
  }

  /**
   * Tests the fetchBaseTables() method.
   */
  public function testFetchBaseTables() {
    $data = $this->viewsData->getAll();

    $base_tables = $this->viewsData->fetchBaseTables();

    // Ensure that 'provider' is set for each base table.
    foreach (array_keys($base_tables) as $base_table) {
      $this->assertEquals('views_test_data', $data[$base_table]['table']['provider'], $base_table);
    }

    // Test the number of tables returned and their order.
    $this->assertCount(6, $base_tables, 'The correct amount of base tables were returned.');
    $base_tables_keys = array_keys($base_tables);
    for ($i = 1; $i < count($base_tables); ++$i) {
      $prev = $base_tables[$base_tables_keys[$i - 1]];
      $current = $base_tables[$base_tables_keys[$i]];
      $this->assertGreaterThanOrEqual($prev['weight'], $current['weight']);
    }

    // Test the values returned for each base table.
    $defaults = [
      'title' => '',
      'help' => '',
      'weight' => 0,
    ];
    foreach ($base_tables as $base_table => $info) {
      // Merge in default values as in fetchBaseTables().
      $expected = $data[$base_table]['table']['base'] += $defaults;
      foreach ($defaults as $key => $default) {
        $this->assertSame($info[$key], $expected[$key]);
      }
    }
  }

  /**
   * Tests fetching all the views data without a static cache.
   */
  public function testGetOnFirstCall() {
    $this->viewsData = new ViewsData(new NullBackend(''), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    $expected_views_data = $this->viewsDataWithProvider();
    $views_data = $this->viewsData->getAll();
    $this->assertArraySubset($expected_views_data, $views_data);
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertTrue($state->get('views_hook_test_views_data', FALSE));
    $this->assertTrue($state->get('views_hook_test_views_data_alter', FALSE));
  }

  /**
   * Tests the cache of the full and single table data.
   */
  public function testFullAndTableGetCache() {
    $expected_views_data = $this->viewsDataWithProvider();
    $table_name = 'views_test_data';
    $table_name_2 = 'views_test_data_2';

    $random_table_name = 'quick_brown_fox';

    // The cache should only be called once (before the clear() call) as get
    // will get all table data in the first get().
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get('views_data:en')
      ->shouldBeCalled()
      ->willReturn(FALSE);
    $cache_backend->set('views_data:en', call_user_func_array([Argument::class, 'allOf'], array_map([Argument::class, 'withEntry'], array_keys($expected_views_data), $expected_views_data)), Argument::cetera())
      ->shouldBeCalled();
    $cache_backend->get("views_data:$random_table_name:en")
      ->shouldBeCalled()
      ->willReturn(FALSE);
    $cache_backend->set("views_data:$random_table_name:en", [], Argument::cetera())
      ->shouldBeCalled()
      ->willReturn(FALSE);

    $cache_tags_invalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $this->container->set('cache_tags.invalidator', $cache_tags_invalidator->reveal());
    $cache_tags_invalidator->invalidateTags(['views_data'])
      ->shouldBeCalled();

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    $views_data = $this->viewsData->getAll();
    $this->assertArraySubset($expected_views_data, $views_data);

    // Request a specific table should be static cached.
    $views_data = $this->viewsData->get($table_name);
    $this->assertSame($expected_views_data[$table_name], $views_data);

    // Another table being requested should also come from the static cache.
    $views_data = $this->viewsData->get($table_name_2);
    $this->assertSame($expected_views_data[$table_name_2], $views_data);

    $views_data = $this->viewsData->get($random_table_name);
    $this->assertSame([], $views_data);

    $this->viewsData->clear();

    // Get the views data again.
    $this->viewsData->getAll();
    $this->viewsData->get($table_name);
    $this->viewsData->get($table_name_2);
    $this->viewsData->get($random_table_name);

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertTrue($state->get('views_hook_test_views_data'));
    $this->assertTrue($state->get('views_hook_test_views_data_alter'));
    $this->assertArraySubset($expected_views_data, $state->get('views_hook_test_views_data_alter_data'));
  }

  /**
   * Tests the caching of the full views data.
   */
  public function testFullGetCache() {
    $expected_views_data = $this->viewsDataWithProvider();

    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get('views_data:en')
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->set('views_data:en', Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    $views_data = $this->viewsData->getAll();
    $this->assertArraySubset($expected_views_data, $views_data);

    $views_data = $this->viewsData->getAll();
    $this->assertArraySubset($expected_views_data, $views_data);

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertArraySubset($expected_views_data, $state->get('views_hook_test_views_data_alter_data'));
  }

  /**
   * Tests the caching of the views data for a specific table.
   */
  public function testSingleTableGetCache() {
    $table_name = 'views_test_data';
    $expected_views_data = $this->viewsDataWithProvider();

    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get("views_data:$table_name:en")
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->get('views_data:en')
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->set("views_data:$table_name:en", Argument::cetera())
      ->shouldBeCalledTimes(1);
    $cache_backend->set('views_data:en', Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    $views_data = $this->viewsData->get($table_name);
    $this->assertSame($expected_views_data[$table_name], $views_data, 'Make sure fetching views data by table works as expected.');

    $views_data = $this->viewsData->get($table_name);
    $this->assertSame($expected_views_data[$table_name], $views_data, 'Make sure fetching cached views data by table works as expected.');

    // Test that this data is present if all views data is returned.
    $views_data = $this->viewsData->getAll();

    $this->assertArrayHasKey($table_name, $views_data, 'Make sure the views_test_data info appears in the total views data.');
    $this->assertSame($expected_views_data[$table_name], $views_data[$table_name], 'Make sure the views_test_data has the expected values.');

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertArraySubset($this->viewsDataWithProvider(), $state->get('views_hook_test_views_data_alter_data'));
  }

  /**
   * Tests building the views data with a non existing table.
   */
  public function testNonExistingTableGetCache() {
    $non_existent_table = 'quick_brown_fox';

    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get("views_data:$non_existent_table:en")
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->get('views_data:en')
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->set("views_data:$non_existent_table:en", [], Argument::cetera())
      ->shouldBeCalledTimes(1);
    $cache_backend->set('views_data:en', Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // All views data should be requested on the first try.
    $views_data = $this->viewsData->get($non_existent_table);
    $this->assertSame([], $views_data, 'Make sure fetching views data for an invalid table returns an empty array.');

    // Test no data is rebuilt when requesting an invalid table again.
    $views_data = $this->viewsData->get($non_existent_table);
    $this->assertSame([], $views_data, 'Make sure fetching views data for an invalid table returns an empty array.');

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertArraySubset($this->viewsDataWithProvider(), $state->get('views_hook_test_views_data_alter_data'));
  }

  /**
   * Tests the cache backend behavior with requesting the same table multiple.
   */
  public function testCacheCallsWithSameTableMultipleTimes() {
    $expected_views_data = $this->viewsDataWithProvider();

    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get('views_data:views_test_data:en')
      ->shouldBeCalledTimes(1);
    $cache_backend->get('views_data:en')
      ->shouldBeCalledTimes(1);
    $cache_backend->set('views_data:en', call_user_func_array([Argument::class, 'allOf'], array_map([Argument::class, 'withEntry'], array_keys($expected_views_data), $expected_views_data)), Argument::cetera())
      ->shouldBeCalledTimes(1);
    $cache_backend->set('views_data:views_test_data:en', $expected_views_data['views_test_data'], Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Request the same table 5 times. The caches are empty at this point, so
    // what will happen is that it will first check for a cache entry for the
    // given table, get a cache miss, then try the cache entry for all tables,
    // which does not exist yet either. As a result, it rebuilds the information
    // and writes a cache entry for all tables and the requested table.
    $table_name = 'views_test_data';
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->get($table_name);
      $this->assertSame($expected_views_data['views_test_data'], $views_data);
    }
  }

  /**
   * Tests the cache calls for a single table and warm cache.
   *
   * Warm cache:
   *   - all tables
   *   - views_test_data
   */
  public function testCacheCallsWithSameTableMultipleTimesAndWarmCache() {
    $expected_views_data = $this->viewsDataWithProvider();

    // Setup a warm cache backend for a single table.
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get('views_data:views_test_data:en')
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => $expected_views_data['views_test_data'],
      ]);
    $cache_backend->set()
      ->shouldNotBeCalled();

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // We have a warm cache now, so this will only request the tables-specific
    // cache entry and return that.
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->get('views_test_data');
      $this->assertSame($expected_views_data['views_test_data'], $views_data);
    }
  }

  /**
   * Tests the cache calls for a different table than the one in cache.
   *
   * Warm cache:
   *   - all tables
   *   - views_test_data
   * Not warm cache:
   *   - views_test_data_2
   */
  public function testCacheCallsWithWarmCacheAndDifferentTable() {
    $expected_views_data = $this->viewsDataWithProvider();

    // Setup a warm cache backend for a single table.
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cid = 'views_data:en';
    $cache_backend->get('views_data:views_test_data_2:en')
      ->shouldBeCalledTimes(1)
      ->willReturn(NULL);
    $cache_backend->get($cid)
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => $expected_views_data,
      ]);
    $cache_backend->set('views_data:views_test_data_2:en', $expected_views_data['views_test_data_2'], Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Requests a different table as the cache contains. This will fail to get a
    // table specific cache entry, load the cache entry for all tables and save
    // a cache entry for this table but not all.
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->get('views_test_data_2');
      $this->assertSame($expected_views_data['views_test_data_2'], $views_data);
    }
  }

  /**
   * Tests the cache calls for a non-existent table.
   *
   * Warm cache:
   *   - all tables
   *   - views_test_data
   * Not warm cache:
   *   - $non_existing_table
   */
  public function testCacheCallsWithWarmCacheAndInvalidTable() {
    $expected_views_data = $this->viewsDataWithProvider();
    $non_existing_table = 'quick_brown_fox';

    // Setup a warm cache backend for a single table.
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $non_existing_cid = "views_data:$non_existing_table:en";
    $cid = 'views_data:en';
    $cache_backend->get($non_existing_cid)
      ->shouldBeCalledTimes(1)
      ->willReturn(NULL);
    $cache_backend->get($cid)
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => $expected_views_data,
      ]);
    $cache_backend->set($non_existing_cid, [], Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Initialize the views data cache and request a non-existing table. This
    // will result in the same cache requests as we explicitly write an empty
    // cache entry for non-existing tables to avoid unnecessary requests in
    // those situations. We do have to load the cache entry for all tables to
    // check if the table does exist or not.
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->get($non_existing_table);
      $this->assertSame([], $views_data);
    }
  }

  /**
   * Tests the cache calls for a non-existent table.
   *
   * Warm cache:
   *   - all tables
   *   - views_test_data
   *   - $non_existing_table
   */
  public function testCacheCallsWithWarmCacheForInvalidTable() {
    $non_existing_table = 'quick_brown_fox';

    // Setup a warm cache backend for a single table.
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cid = "views_data:$non_existing_table:en";
    $cache_backend->get($cid)
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => [],
      ]);
    $cache_backend->set($cid)
      ->shouldNotBeCalled();

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Initialize the views data cache and request a non-existing table. This
    // will result in the same cache requests as we explicitly write an empty
    // cache entry for non-existing tables to avoid unnecessary requests in
    // those situations. We do have to load the cache entry for all tables to
    // check if the table does exist or not.
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->get($non_existing_table);
      $this->assertSame([], $views_data);
    }
  }

  /**
   * Tests the cache calls for all views data without a warm cache.
   */
  public function testCacheCallsWithoutWarmCacheAndGetAllTables() {
    $expected_views_data = $this->viewsDataWithProvider();

    $cid = 'views_data:en';
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get($cid)
      ->shouldBeCalled()
      ->willReturn(NULL);
    $cache_backend->set($cid, call_user_func_array([Argument::class, 'allOf'], array_map([Argument::class, 'withEntry'], array_keys($expected_views_data), $expected_views_data)), Argument::cetera())
      ->shouldBeCalled();

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Initialize the views data cache and repeat with no specified table. This
    // should only load the cache entry for all tables.
    for ($i = 0; $i < 5; $i++) {
      $this->assertArraySubset($expected_views_data, $this->viewsData->getAll());
    }
  }

  /**
   * Tests the cache calls for all views data.
   *
   * Warm cache:
   *   - all tables
   */
  public function testCacheCallsWithWarmCacheAndGetAllTables() {
    $expected_views_data = $this->viewsDataWithProvider();

    $cid = 'views_data:en';
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get($cid)
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => $expected_views_data,
      ]);
    $cache_backend->set()
      ->shouldNotBeCalled();

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    // Initialize the views data cache and repeat with no specified table. This
    // should only load the cache entry for all tables.
    for ($i = 0; $i < 5; $i++) {
      $views_data = $this->viewsData->getAll();
      $this->assertSame($expected_views_data, $views_data);
    }
  }

  /**
   * Tests the cache calls for multiple tables without warm caches.
   *
   * @covers ::get
   */
  public function testCacheCallsWithoutWarmCacheAndGetMultipleTables() {
    $expected_views_data = $this->viewsDataWithProvider();
    $table_name = 'views_test_data';
    $table_name_2 = 'views_test_data_2';

    // Setup a warm cache backend for all table data, but not single tables.
    /** @var \Drupal\Core\Cache\CacheBackendInterface|\Prophecy\Prophecy\ProphecyInterface $cache_backend */
    $cache_backend = $this->prophesize(CacheBackendInterface::class);
    $cache_backend->get("views_data:$table_name:en")
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->get("views_data:$table_name_2:en")
      ->shouldBeCalledTimes(1)
      ->willReturn(FALSE);
    $cache_backend->get('views_data:en')
      ->shouldBeCalledTimes(1)
      ->willReturn((object) [
        'data' => $expected_views_data,
      ]);
    $cache_backend->set("views_data:$table_name:en", $expected_views_data[$table_name], Argument::cetera())
      ->shouldBeCalledTimes(1);
    $cache_backend->set("views_data:$table_name_2:en", $expected_views_data[$table_name_2], Argument::cetera())
      ->shouldBeCalledTimes(1);

    $this->viewsData = new ViewsData($cache_backend->reveal(), $this->container->get('config.factory'), $this->container->get('module_handler'), $this->container->get('language_manager'));

    $this->assertSame($expected_views_data[$table_name], $this->viewsData->get($table_name));
    $this->assertSame($expected_views_data[$table_name_2], $this->viewsData->get($table_name_2));

    // Should only be invoked the first time.
    $this->assertSame($expected_views_data[$table_name], $this->viewsData->get($table_name));
    $this->assertSame($expected_views_data[$table_name_2], $this->viewsData->get($table_name_2));
  }

  /**
   * Tests that getting data with an empty key throws an exception.
   *
   * @covers ::get
   * @dataProvider providerTestGetEmptyKey
   */
  public function testGetEmptyKey($key) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('A valid cache entry key is required. Use getAll() to get all table data.');

    $this->viewsData->get($key);
  }

  public function providerTestGetEmptyKey() {
    return [
      [NULL],
      [''],
      [0],
    ];
  }

}
