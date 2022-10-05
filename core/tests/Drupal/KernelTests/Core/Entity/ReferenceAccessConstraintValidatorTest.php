<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test_revlog\Entity\EntityTestMulWithRevisionLog;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests validation constraints for ReferenceAccessConstraintValidator.
 *
 * @group Validation
 */
class ReferenceAccessConstraintValidatorTest extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test_revlog', 'file', 'image'];

  /**
   * Storage for test images.
   *
   * @var \Drupal\file\Entity\File[]
   */
  protected $testImages = [];

  /**
   *
   * @var mixed
   */
  protected $noAccessUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test_mul_revlog');
    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installConfig(['user']);
    $this->installSchema('file', ['file_usage']);

    $this->setupImages();
    $this->setupEntityFields();

    $this->noAccessUser = User::create(['name' => 'no access user']);
    $this->noAccessUser->save();

    $admin = User::load(1);
    $this->container->get('account_switcher')->switchTo($admin);
  }

  /**
   * Helper function to create the test images needed for the tests.
   */
  public function setupImages() {
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->testImages[0] = File::create([
      'uri' => 'public://example.jpg',
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $this->testImages[0]->save();

    $this->testImages[1] = File::create([
      'uri' => 'public://example.jpg',
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $this->testImages[1]->save();
  }

  /**
   * Helper function to create the entity fields needed for the tests.
   */
  public function setupEntityFields() {
    foreach (['entity_test', 'entity_test_mul_revlog'] as $entity_type) {
      FieldStorageConfig::create([
        'entity_type' => $entity_type,
        'field_name' => 'image_test',
        'type' => 'image',
      ])->save();
      FieldConfig::create([
        'entity_type' => $entity_type,
        'field_name' => 'image_test',
        'bundle' => $entity_type,
        'settings' => [
          'file_extensions' => 'jpg',
        ],
      ])->save();
    }
  }

  /**
   * Test that validating pre-existing items skip permission checks.
   */
  public function testPreExistingItemsValidation() {
    $referencing_entity = EntityTest::create([
      'type' => 'entity_test',
    ]);

    $referencing_entity->image_test->target_id = $this->testImages[0]->id();
    $referencing_entity->image_test->alt = $this->randomMachineName();
    $referencing_entity->image_test->title = $this->randomMachineName();
    $referencing_entity->name->value = $this->randomMachineName();
    $referencing_entity->save();

    // We switch to a user with no access to validate that the referenced entity
    // access check is skipped with pre-existing content.
    $this->container->get('account_switcher')->switchTo($this->noAccessUser);
    $violations = $referencing_entity->image_test->validate();
    $this->assertCount(0, $violations);
  }

  /**
   * Test that validating pre-existing items on specific content revisions skip permission checks.
   */
  public function testPreExistingItemsValidationOnRevisions() {
    $referencing_entity = EntityTestMulWithRevisionLog::create([
      'type' => 'entity_test_mul_revlog',
    ]);
    $referencing_entity->image_test->target_id = $this->testImages[0]->id();
    $referencing_entity->image_test->alt = $this->randomMachineName();
    $referencing_entity->image_test->title = $this->randomMachineName();
    $referencing_entity->name->value = $this->randomMachineName();

    $violations = $referencing_entity->image_test->validate();
    $this->assertCount(0, $violations);
    $referencing_entity->save();

    // We create a non default revision with a different image.
    $referencing_entity->image_test->target_id = $this->testImages[1]->id();
    $referencing_entity->image_test->alt = $this->randomMachineName();
    $referencing_entity->image_test->title = $this->randomMachineName();
    $referencing_entity->setNewRevision();
    $referencing_entity->isDefaultRevision(FALSE);

    $violations = $referencing_entity->image_test->validate();
    $this->assertCount(0, $violations);
    $referencing_entity->save();

    // We switch to a user with no access to validate that the referenced entity
    // access check is skipped with pre-existing content.
    $this->container->get('account_switcher')->switchTo($this->noAccessUser);
    $referencing_entity->setNewRevision();
    $referencing_entity->isDefaultRevision(FALSE);

    $violations = $referencing_entity->image_test->validate();
    $this->assertCount(0, $violations);
  }

}
