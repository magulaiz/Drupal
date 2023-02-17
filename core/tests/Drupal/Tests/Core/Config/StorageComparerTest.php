<?php

namespace Drupal\Tests\Core\Config;

use Drupal\Component\Uuid\Php;
use Drupal\Core\Config\StorageComparer;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Config\StorageComparer
 * @group Config
 */
class StorageComparerTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Config\StorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $sourceStorage;

  /**
   * @var \Drupal\Core\Config\StorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $targetStorage;

  /**
   * The storage comparer to test.
   *
   * @var \Drupal\Core\Config\StorageComparer
   */
  protected $storageComparer;

  /**
   * An array of test configuration data keyed by configuration name.
   *
   * @var array
   */
  protected $configData;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->sourceStorage = $this->createMock('Drupal\Core\Config\StorageInterface');
    $this->targetStorage = $this->createMock('Drupal\Core\Config\StorageInterface');
    $this->storageComparer = new StorageComparer($this->sourceStorage, $this->targetStorage);
  }

  protected function getConfigData() {
    $uuid = new Php();
    // Mock data using minimal data to use ConfigDependencyManger.
    $this->configData = [
      // Simple config that controls configuration sync.
      'system.site' => [
        'title' => 'Drupal',
        'uuid' => $uuid->generate(),
      ],
      // Config entity which requires another config entity.
      'field.field.node.article.body' => [
        'id' => 'node.article.body',
        'uuid' => $uuid->generate(),
        'dependencies' => [
          'config' => [
            'field.storage.node.body',
          ],
        ],
      ],
      // Config entity which is required by another config entity.
      'field.storage.node.body' => [
        'id' => 'node.body',
        'uuid' => $uuid->generate(),
        'dependencies' => [
          'module' => [
            'text',
          ],
        ],
      ],
      // Config entity not which has no dependencies on configuration.
      'views.view.test_view' => [
        'id' => 'test_view',
        'uuid' => $uuid->generate(),
        'dependencies' => [
          'module' => [
            'node',
          ],
        ],
      ],
      // Simple config.
      'system.performance' => [
        'stale_file_threshold' => 2592000,
      ],

    ];
    return $this->configData;
  }

  /**
   * @covers ::createChangelist
   */
  public function testCreateChangelistNoChange() {
    $config_data = $this->getConfigData();
    $config_files = array_keys($config_data);
    $this->sourceStorage->expects($this->once())
      ->method('listAll')
      ->willReturn($config_files);
    $this->targetStorage->expects($this->once())
      ->method('listAll')
      ->willReturn($config_files);
    $this->sourceStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($config_data);
    $this->targetStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($config_data);
    $this->sourceStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);
    $this->targetStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);

    $this->storageComparer->createChangelist();
    $this->assertEmpty($this->storageComparer->getChangelist('create'));
    $this->assertEmpty($this->storageComparer->getChangelist('delete'));
    $this->assertEmpty($this->storageComparer->getChangelist('update'));
  }

  /**
   * @covers ::createChangelist
   */
  public function testCreateChangelistCreate() {
    $target_data = $source_data = $this->getConfigData();
    unset($target_data['field.storage.node.body']);
    unset($target_data['field.field.node.article.body']);
    unset($target_data['views.view.test_view']);

    $this->sourceStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($source_data));
    $this->targetStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($target_data));
    $this->sourceStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($source_data);
    $this->targetStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($target_data);
    $this->sourceStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);
    $this->targetStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);

    $this->storageComparer->createChangelist();
    $expected = [
      'field.storage.node.body',
      'field.field.node.article.body',
      'views.view.test_view',
    ];
    $this->assertEquals($expected, $this->storageComparer->getChangelist('create'));
    $this->assertEmpty($this->storageComparer->getChangelist('delete'));
    $this->assertEmpty($this->storageComparer->getChangelist('update'));
  }

  /**
   * @covers ::createChangelist
   */
  public function testCreateChangelistDelete() {
    $target_data = $source_data = $this->getConfigData();
    unset($source_data['field.storage.node.body']);
    unset($source_data['field.field.node.article.body']);
    unset($source_data['views.view.test_view']);

    $this->sourceStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($source_data));
    $this->targetStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($target_data));
    $this->sourceStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($source_data);
    $this->targetStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($target_data);
    $this->sourceStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);
    $this->targetStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);

    $this->storageComparer->createChangelist();
    $expected = [
      'views.view.test_view',
      'field.field.node.article.body',
      'field.storage.node.body',
    ];
    $this->assertEquals($expected, $this->storageComparer->getChangelist('delete'));
    $this->assertEmpty($this->storageComparer->getChangelist('create'));
    $this->assertEmpty($this->storageComparer->getChangelist('update'));
  }

  /**
   * @covers ::createChangelist
   */
  public function testCreateChangelistUpdate() {
    $target_data = $source_data = $this->getConfigData();
    $source_data['system.site']['title'] = 'Drupal New!';
    $source_data['field.field.node.article.body']['new_config_key'] = 'new data';
    $source_data['field.storage.node.body']['new_config_key'] = 'new data';

    $this->sourceStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($source_data));
    $this->targetStorage->expects($this->once())
      ->method('listAll')
      ->willReturn(array_keys($target_data));
    $this->sourceStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($source_data);
    $this->targetStorage->expects($this->once())
      ->method('readMultiple')
      ->willReturn($target_data);
    $this->sourceStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);
    $this->targetStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn([]);

    $this->storageComparer->createChangelist();
    $expected = [
      'field.storage.node.body',
      'field.field.node.article.body',
      'system.site',
    ];
    $this->assertEquals($expected, $this->storageComparer->getChangelist('update'));
    $this->assertEmpty($this->storageComparer->getChangelist('create'));
    $this->assertEmpty($this->storageComparer->getChangelist('delete'));
  }

  /**
   * @covers ::createChangelist
   */
  public function testCreateChangelistRecreate() {
    $target_data = $source_data = [
      '' => $this->getConfigData(),
      'language.nl' => [
        'field.field.node.article.body' => [
          'title' => 'Dutch article body title',
        ],
        'field.storage.node.body' => [
          'title' => 'Dutch article body storage title',
        ],
        'views.view.test_view' => [
          'title' => 'Dutch test_view title',
        ],
      ],
    ];

    $uuid = new Php();

    // field.field.node.article.body: only change Dutch translation.
    $source_data['language.nl']['field.field.node.article.body']['title'] = 'New Dutch article body title';
    // field.storage.node.body: recreate default, don't change override.
    $source_data['']['field.storage.node.body']['uuid'] = $uuid->generate();
    // views.view.test_view: recreate parent, change override.
    $source_data['']['views.view.test_view']['uuid'] = $uuid->generate();
    $source_data['language.nl']['views.view.test_view']['title'] = 'New Dutch test_view title';

    $this->sourceStorage->expects($this->atLeastOnce())
      ->method('listAll')
      ->will($this->returnValue(array_keys($source_data[''])));
    $this->targetStorage->expects($this->atLeastOnce())
      ->method('listAll')
      ->will($this->returnValue(array_keys($target_data[''])));
    $this->sourceStorage->expects($this->atLeastOnce())
      ->method('readMultiple')
      ->will($this->returnValue($source_data['']));
    $this->targetStorage->expects($this->atLeastOnce())
      ->method('readMultiple')
      ->will($this->returnValue($target_data['']));
    $this->sourceStorage->expects($this->atLeastOnce())
      ->method('getAllCollectionNames')
      ->will($this->returnValue(array_keys($source_data)));
    $this->targetStorage->expects($this->atLeastOnce())
      ->method('getAllCollectionNames')
      ->will($this->returnValue(array_keys($target_data)));

    $source_collection_storage = $this->createMock('Drupal\Core\Config\StorageInterface');
    $target_collection_storage = $this->createMock('Drupal\Core\Config\StorageInterface');

    $source_collection_storage->expects($this->atLeastOnce())
      ->method('listAll')
      ->will($this->returnValue(array_keys($source_data['language.nl'])));
    $target_collection_storage->expects($this->atLeastOnce())
      ->method('listAll')
      ->will($this->returnValue(array_keys($target_data['language.nl'])));
    $source_collection_storage->expects($this->atLeastOnce())
      ->method('readMultiple')
      ->will($this->returnValue($source_data['language.nl']));
    $target_collection_storage->expects($this->atLeastOnce())
      ->method('readMultiple')
      ->will($this->returnValue($target_data['language.nl']));
    $source_collection_storage->expects($this->atLeastOnce())
      ->method('getCollectionName')
      ->will($this->returnValue('language.nl'));
    $target_collection_storage->expects($this->atLeastOnce())
      ->method('getCollectionName')
      ->will($this->returnValue('language.nl'));

    $this->sourceStorage->expects($this->atLeastOnce())
      ->method('createCollection')
      ->will($this->returnValue($source_collection_storage));
    $this->targetStorage->expects($this->atLeastOnce())
      ->method('createCollection')
      ->will($this->returnValue($target_collection_storage));

    $this->storageComparer->createChangelist();

    $changelist_default = [
      'create' => [
        'field.storage.node.body',
        'views.view.test_view',
      ],
      'update' => [],
      'delete' => [
        'views.view.test_view',
        'field.storage.node.body',
      ],
      'rename' => [],
    ];
    $changelist_nl = [
      'create' => [
        'field.storage.node.body',
        'views.view.test_view',
      ],
      'update' => [
        'field.field.node.article.body',
      ],
      'delete' => [
        'views.view.test_view',
        'field.storage.node.body',
      ],
      'rename' => [],
    ];

    $this->assertEquals($changelist_default, $this->storageComparer->getChangelist());
    $this->assertEquals($changelist_nl, $this->storageComparer->getChangelist(NULL, 'language.nl'));
  }

}
