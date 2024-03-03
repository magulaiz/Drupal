<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Entity\ContentEntityStorageBase
 *
 * @group Entity
 */
class ContentEntityStorageBaseTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');
  }

  /**
   * @covers ::create
   *
   * @dataProvider providerTestCreate
   */
  public function testCreate($bundle) {
    $storage = $this->container->get('entity_type.manager')->getStorage('entity_test');

    $entity = $storage->create(['type' => $bundle]);
    $this->assertEquals('test_bundle', $entity->bundle());
  }

  /**
   * Provides test data for testCreate().
   */
  public static function providerTestCreate() {
    return [
      ['scalar' => 'test_bundle'],
      ['array keyed by delta' => [0 => ['value' => 'test_bundle']]],
      ['array keyed by main property name' => ['value' => 'test_bundle']],
    ];
  }

  /**
   * @covers ::create
   */
  public function testReCreate() {
    $storage = $this->container->get('entity_type.manager')->getStorage('entity_test');

    $values = $storage->create(['type' => 'test_bundle'])->toArray();
    $entity = $storage->create($values);
    $this->assertEquals('test_bundle', $entity->bundle());
  }

  /**
   * @covers ::create
   * @covers ::doCreate
   *
   * @see https://www.drupal.org/project/drupal/issues/3425226
   */
  public function testCreateWithIdSet(): void {
    \Drupal::state()->set('ContentEntityStorageBaseTest__testCreateWithIdSet', TRUE);
    // Clear cache to refresh field type definitions.
    drupal_flush_all_caches();

    $entity = EntityTest::create([
      'id' => 123,
      'name' => 'The Name',
    ]);
    $this->assertSame('The Name (new)', $entity->label());
  }

}
