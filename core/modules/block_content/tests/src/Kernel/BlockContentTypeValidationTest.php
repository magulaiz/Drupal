<?php

namespace Drupal\Tests\block_content\Kernel;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of block_content_type entities.
 *
 * @group block_content
 */
class BlockContentTypeValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block_content'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = BlockContentType::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the block content type's ID is validated as a machine name.
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
