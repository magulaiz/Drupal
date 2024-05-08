<?php

declare(strict_types=1);

namespace Drupal\Tests\workspaces\Kernel;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test\Entity\EntityTestMulRevPub;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @group workspaces
 */
#[CoversClass(\Drupal\workspaces\Plugin\Validation\Constraint\EntityReferenceSupportedNewEntitiesConstraintValidator::class)]
class EntityReferenceSupportedNewEntitiesConstraintValidatorTest extends KernelTestBase {

  use UserCreationTrait;
  use WorkspaceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'workspaces',
    'entity_test',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->createUser();

    $fields['supported_reference'] = BaseFieldDefinition::create('entity_reference')->setSetting('target_type', 'entity_test_mulrevpub');
    $fields['unsupported_reference'] = BaseFieldDefinition::create('entity_reference')->setSetting('target_type', 'entity_test');
    $this->container->get('state')->set('entity_test_mulrevpub.additional_base_field_definitions', $fields);

    $this->installEntitySchema('entity_test_mulrevpub');
    $this->initializeWorkspacesModule();
  }

  public function testNewEntitiesAllowedInDefaultWorkspace() {
    $entity = EntityTestMulRevPub::create([
      'unsupported_reference' => [
        'entity' => EntityTest::create([]),
      ],
      'supported_reference' => [
        'entity' => EntityTest::create([]),
      ],
    ]);
    $this->assertCount(0, $entity->validate());
  }

  public function testNewEntitiesForbiddenInNonDefaultWorkspace() {
    $this->switchToWorkspace('stage');
    $entity = EntityTestMulRevPub::create([
      'unsupported_reference' => [
        'entity' => EntityTest::create([]),
      ],
      'supported_reference' => [
        'entity' => EntityTestMulRevPub::create([]),
      ],
    ]);
    $violations = $entity->validate();
    $this->assertCount(1, $violations);
    $this->assertEquals('Test entity entities can only be created in the default workspace.', $violations[0]->getMessage());
  }

}
