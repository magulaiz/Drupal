<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Tests the ContentEntityStorageBase::createWithSampleValues method.
 *
 * @coversDefaultClass \Drupal\Core\Entity\ContentEntityStorageBase
 * @group Entity
 */
class CreateSampleEntityTest extends KernelTestBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'path_alias',
    'system',
    'field',
    'filter',
    'text',
    'file',
    'user',
    'node',
    'comment',
    'taxonomy',
    'menu_link_content',
    'link',
    'content_translation',
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('system', ['sequences']);

    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('node_type');
    $this->installEntitySchema('comment');
    $this->installEntitySchema('comment_type');
    $this->installEntitySchema('path_alias');
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('menu_link_content');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();
    NodeType::create(['type' => 'page', 'name' => 'Page'])->save();
    Vocabulary::create(['name' => 'Tags', 'vid' => 'tags'])->save();
  }

  /**
   * Tests sample value content entity creation of all types.
   *
   * @covers ::createWithSampleValues
   */
  public function testSampleValueContentEntity() {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $definition) {
      $entity_storage = $this->entityTypeManager->getStorage($entity_type_id);
      if ($entity_storage instanceof ContentEntityStorageInterface) {
        $values = [];
        if ($label = $definition->getKey('label')) {
          $values[$label] = $this->randomString();
        }

        // Create sample entities with bundles.
        if ($bundle_type = $definition->getBundleEntityType()) {
          $bundles = array_map(function (EntityInterface $entity_type) {
            return $entity_type->id();
          }, $this->entityTypeManager->getStorage($bundle_type)->loadMultiple());
        }
        // Create sample entities without bundles.
        else {
          $bundles = [FALSE];
        }
        foreach ($bundles as $bundle) {
          $entity = $entity_storage->createWithSampleValues($bundle, $values);
          $entity->save();
          $entity_storage->resetCache([$entity->id()]);
          $entity = $entity_storage->load($entity->id());
          $violations = $entity->validate();
          $this->assertCount(0, $violations, (string) $violations);
          if ($label) {
            $this->assertEquals($values[$label], $entity->label());
          }
        }
      }
    }
  }

}
