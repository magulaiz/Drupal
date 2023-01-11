<?php

namespace Drupal\Tests\comment\Kernel;

use Drupal\comment\Entity\CommentType;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of comment_type entities.
 *
 * @group comment
 */
class CommentTypeValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['comment', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = CommentType::create([
      'id' => 'test',
      'label' => 'Test',
      'target_entity_type_id' => 'node',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the comment type's ID is validated as a machine name.
   *
   * @param string $invalid_id
   *   An invalid machine name that should raise a validation error.
   *
   * @testWith ["invalid name"]
   *  ["invalid-name"]
   *  ["Invalid_Name"]
   */
  public function testMachineName(string $invalid_id): void {
    $this->entity->set('id', $invalid_id);
    $this->assertValidationErrors(['This value is not valid.']);

    $this->entity->set('id', mb_strtolower($this->randomMachineName(68)));
    $this->assertValidationErrors(['This value is too long. It should have <em class="placeholder">64</em> characters or less.']);
  }

}
