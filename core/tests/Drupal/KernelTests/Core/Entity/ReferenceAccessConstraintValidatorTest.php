<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
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
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $image;

  protected $limitedAccessUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test_mul_revlog');
    $this->installEntitySchema('user');
    $this->installConfig(['user']);

    $this->limitedAccessUser = User::create(['name' => 'limited access user']);
    $this->limitedAccessUser->save();

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'image_test',
      'type' => 'image',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'image_test',
      'bundle' => 'entity_test',
      'settings' => [
        'file_extensions' => 'jpg',
      ],
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'rev_image_test',
      'entity_type' => 'entity_test_mul_revlog',
      'type' => 'image',
    ])->save();
    FieldConfig::create([
      'field_name' => 'rev_image_test',
      'entity_type' => 'entity_test_mul_revlog',
      'bundle' => 'entity_test_mul_revlog',
      'settings' => [
        'file_extensions' => 'jpg',
      ],
    ])->save();
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = File::create([
      'uri' => 'public://example.jpg',
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $this->image->save();
  }

  /**
   * Test that validating pre-existing items skip permission checks.
   */
  // public function testPreExistingItemsValidation() {
  //   $referencing_entity = EntityTest::create([
  //     'type' => 'entity_test',
  //   ]);

  //   $referencing_entity->image_test->target_id = $this->image->id();
  //   $referencing_entity->image_test->alt = $this->randomMachineName();
  //   $referencing_entity->image_test->title = $this->randomMachineName();
  //   $referencing_entity->name->value = $this->randomMachineName();
  //   $referencing_entity->save();

  //   // Check that users with no access are able pass the validation for fields
  //   // with pre-existing content.
  //   $this->container->get('account_switcher')->switchTo($this->limitedAccessUser);
  //   $violations = $referencing_entity->image_test->validate();
  //   $this->assertCount(0, $violations);
  // }

  /**
   * Test that validating pre-existing items on specific content revision skip permission checks.
   */
  public function testPreExistingItemsValidationOnRevisions() {
    $admin = User::load(1);
    $this->container->get('account_switcher')->switchTo($admin);
    $revision_ids = [];

    // Create the initial default revision.
    $referencing_entity = EntityTestMulWithRevisionLog::create([
      'type' => 'entity_test_mul_revlog',
    ]);
    $referencing_entity->rev_image_test->target_id = $this->image->id();
    $referencing_entity->rev_image_test->alt = $this->randomMachineName();
    $referencing_entity->rev_image_test->title = $this->randomMachineName();
    $referencing_entity->name->value = $this->randomMachineName();
    $violations = $referencing_entity->rev_image_test->validate();
    $this->assertCount(0, $violations);

    $referencing_entity->save();
    $revision_ids[] = $referencing_entity->getRevisionId();
    dump(sprintf('Revision saved: %s', $referencing_entity->getRevisionId()));

    // Create a second revision.
    $this->container->get('account_switcher')->switchTo($this->limitedAccessUser);
    $referencing_entity->setNewRevision();
    $referencing_entity->isDefaultRevision(FALSE);

    $violations = $referencing_entity->rev_image_test->validate();
    $this->assertCount(0, $violations);

    $referencing_entity->save();
    $revision_ids[] = $referencing_entity->getRevisionId();
    dump(sprintf('Revision saved: %s', $referencing_entity->getRevisionId()));

    // Create a third revision.
    // $referencing_entity->rev_image_test->target_id = $this->image->id();
    // $referencing_entity->rev_image_test->alt = $this->randomMachineName();
    // $referencing_entity->rev_image_test->title = $this->randomMachineName();
    $referencing_entity->setNewRevision();
    $referencing_entity->isDefaultRevision(FALSE);

    $violations = $referencing_entity->rev_image_test->validate();
    $this->assertCount(0, $violations);

    $referencing_entity->save();
    $revision_ids[] = $referencing_entity->getRevisionId();
    dump(sprintf('Revision saved: %s', $referencing_entity->getRevisionId()));
  }

}
