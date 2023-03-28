<?php

namespace Drupal\Tests\content_moderation\Kernel;

use Drupal\content_moderation\Entity\Handler\ModerationHandler;
use Drupal\content_moderation\EntityTypeInfo;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;

/**
 * @coversDefaultClass \Drupal\content_moderation\EntityTypeInfo
 *
 * @group content_moderation
 */
class EntityTypeInfoTest extends KernelTestBase {

  use ContentModerationTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'content_moderation',
    'workflows',
    'entity_test',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type info class.
   *
   * @var \Drupal\content_moderation\EntityTypeInfo
   */
  protected $entityTypeInfo;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeInfo = $this->container->get('class_resolver')->getInstanceFromDefinition(EntityTypeInfo::class);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->entityFieldManager = $this->container->get('entity_field.manager');

    $this->installConfig(['content_moderation']);
  }

  /**
   * @covers ::entityBundleFieldInfo
   */
  public function testEntityBundleFieldInfo() {
    $definition = $this->entityTypeManager->getDefinition('entity_test');
    $definition->setHandlerClass('moderation', ModerationHandler::class);

    $this->enableModeration('entity_test', 'entity_test');
    $bundle_fields = $this->entityTypeInfo->entityBundleFieldInfo($definition, 'entity_test', []);

    $this->assertFalse($bundle_fields['moderation_state']->isReadOnly());
    $this->assertTrue($bundle_fields['moderation_state']->isComputed());
    $this->assertTrue($bundle_fields['moderation_state']->isTranslatable());
  }

  /**
   * Tests the correct entity types have moderation added.
   *
   * @covers ::entityTypeAlter
   *
   * @dataProvider providerTestEntityTypeAlter
   */
  public function testEntityTypeAlter($entity_type_id, $moderatable) {
    $entity_types = $this->entityTypeManager->getDefinitions();
    $this->assertSame($moderatable, $entity_types[$entity_type_id]->hasHandlerClass('moderation'));
  }

  /**
   * Provides test data for testEntityTypeAlter().
   *
   * @return array
   *   An array of test cases, where each test case is an array with the
   *   following values:
   *   - An entity type ID.
   *   - Whether the entity type is moderatable or not.
   */
  public function providerTestEntityTypeAlter() {
    $tests = [];
    $tests['non_internal_non_revisionable'] = ['entity_test', FALSE];
    $tests['non_internal_revisionable'] = ['entity_test_rev', TRUE];
    $tests['internal_non_revisionable'] = ['entity_test_no_label', FALSE];
    $tests['internal_revisionable'] = ['content_moderation_state', FALSE];
    return $tests;
  }

  /**
   * @covers ::entityBundleFieldInfo
   */
  public function testBundleFieldOnlyAddedToModeratedEntityTypes() {
    $definition = $this->entityTypeManager->getDefinition('entity_test_with_bundle');

    EntityTestBundle::create([
      'id' => 'moderated',
    ])->save();
    EntityTestBundle::create([
      'id' => 'unmoderated',
    ])->save();

    $this->assertArrayNotHasKey('moderation_state', $this->entityTypeInfo->entityBundleFieldInfo($definition, 'moderated', []));
    $this->assertArrayNotHasKey('moderation_state', $this->entityTypeInfo->entityBundleFieldInfo($definition, 'unmoderated', []));

    $this->enableModeration('entity_test_with_bundle', 'moderated');

    $this->assertArrayHasKey('moderation_state', $this->entityTypeInfo->entityBundleFieldInfo($definition, 'moderated', []));
    $this->assertArrayNotHasKey('moderation_state', $this->entityTypeInfo->entityBundleFieldInfo($definition, 'unmoderated', []));
  }

  /**
   * @covers ::entityBundleFieldInfo
   */
  public function testBundleFieldOnlyAddedToModeratedBundles() {
    EntityTestBundle::create([
      'id' => 'foo',
    ])->save();

    EntityTestBundle::create([
      'id' => 'bar',
    ])->save();

    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle('entity_test_with_bundle', 'foo');
    $workflow->getTypePlugin()->addEntityTypeAndBundle('entity_test_with_bundle', 'bar');
    $workflow->save();

    $entity_field_manager = \Drupal::service('entity_field.manager');
    $foo_field = $entity_field_manager->getFieldDefinitions('entity_test_with_bundle', 'foo')['moderation_state'];
    $bar_field = $entity_field_manager->getFieldDefinitions('entity_test_with_bundle', 'bar')['moderation_state'];
    $this->assertEquals($foo_field->getTargetBundle(), 'foo');
    $this->assertEquals($bar_field->getTargetBundle(), 'bar');
  }

  /**
   * Tests entity base field provider.
   */
  public function testEntityBaseFieldProvider() {
    $this->enableModeration('entity_test_mulrev', 'entity_test_mulrev');
    $this->container->get('state')->set('entity_test.field_test_item', TRUE);

    $field_definitions = $this->entityFieldManager->getFieldDefinitions('entity_test_mulrev', 'entity_test_mulrev');
    $this->assertEquals('entity_test', $field_definitions['field_test_item']->getProvider());
  }

  /**
   * Add moderation to an entity type and bundle.
   */
  protected function enableModeration($entity_type_id, $bundle) {
    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle($entity_type_id, $bundle);
    $workflow->save();
  }

}
