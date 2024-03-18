<?php

namespace Drupal\Tests\workspaces\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\workspaces\Entity\Workspace;

/**
 * Tests entity translations with workspaces.
 *
 * @group workspaces
 */
class WorkspaceContentTranslationTest extends KernelTestBase {

  use UserCreationTrait;
  use WorkspaceTestTrait;

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
    'content_translation',
    'entity_test',
    'language',
    'path_alias',
    'user',
    'workspaces',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = \Drupal::entityTypeManager();

    $this->installEntitySchema('entity_test_mulrevpub');
    $this->installEntitySchema('user');
    $this->installEntitySchema('workspace');

    $this->installConfig(['language', 'content_translation']);

    $this->installSchema('workspaces', ['workspace_association']);

    $language = ConfigurableLanguage::createFromLangcode('ro');
    $language->save();

    $this->container->get('content_translation.manager')
      ->setEnabled('entity_test_mulrevpub', 'entity_test_mulrevpub', TRUE);

    Workspace::create(['id' => 'stage', 'label' => 'Stage'])->save();
  }

  /**
   * Tests translations created in a workspace.
   *
   * @covers \Drupal\workspaces\EntityOperations::entityTranslationInsert
   */
  public function testTranslations(): void {
    $storage = $this->entityTypeManager->getStorage('entity_test_mulrevpub');

    // Create two untranslated nodes in Live, a published and an unpublished one.
    $entity_pub = $storage->create(['name' => 'live - 1 - published', 'status' => TRUE]);
    $entity_pub->save();
    $entity_unpub = $storage->create(['name' => 'live - 2 - unpublished', 'status' => FALSE]);
    $entity_unpub->save();

    // Activate the Stage workspace and start adding translations.
    $this->switchToWorkspace('stage');

    // Add a translation for each entity.
    $entity_pub->addTranslation('ro', ['name' => 'live - 1 - published - RO']);
    $entity_pub->save();

    $entity_unpub->addTranslation('ro', ['name' => 'live - 2 - unpublished - RO']);
    $entity_unpub->save();

    // Both 'EN' and 'RO' translations are published in Stage.
    $entity_pub = $storage->loadUnchanged($entity_pub->id());
    $this->assertTrue($entity_pub->isPublished());
    $this->assertEquals('live - 1 - published', $entity_pub->get('name')->value);

    $translation = $entity_pub->getTranslation('ro');
    $this->assertTrue($translation->isPublished());
    $this->assertEquals('live - 1 - published - RO', $translation->get('name')->value);

    // Both 'EN' and 'RO' translations are unpublished in Stage.
    $entity_unpub = $storage->loadUnchanged($entity_unpub->id());
    $this->assertFalse($entity_unpub->isPublished());
    $this->assertEquals('live - 2 - unpublished', $entity_unpub->get('name')->value);

    $translation = $entity_unpub->getTranslation('ro');
    $this->assertEquals('live - 2 - unpublished - RO', $translation->get('name')->value);
    $this->assertTrue($translation->isPublished());

    // Switch to Live and check the translations.
    $this->switchToLive();

    // The 'EN' translation is still published in Live, but the 'RO' one is
    // unpublished.
    $entity_pub = $storage->loadUnchanged($entity_pub->id());
    $this->assertTrue($entity_pub->isPublished());
    $this->assertEquals('live - 1 - published', $entity_pub->get('name')->value);

    $translation = $entity_pub->getTranslation('ro');
    $this->assertFalse($translation->isPublished());
    $this->assertEquals('live - 1 - published - RO', $translation->get('name')->value);

    // Both 'EN' and 'RO' translations are unpublished in Live.
    $entity_unpub = $storage->loadUnchanged($entity_unpub->id());
    $this->assertFalse($entity_unpub->isPublished());
    $this->assertEquals('live - 2 - unpublished', $entity_unpub->get('name')->value);

    $translation = $entity_unpub->getTranslation('ro');
    $this->assertFalse($translation->isPublished());
    $this->assertEquals('live - 2 - unpublished - RO', $translation->get('name')->value);
  }

}
