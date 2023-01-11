<?php

namespace Drupal\Tests\block\Kernel;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of block entities.
 *
 * @group block
 */
class BlockValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = Block::create([
      'id' => 'test_block',
      'theme' => 'stark',
      'plugin' => 'system_powered_by_block',
    ]);
    $this->entity->save();
  }

  /**
   * Tests validating a block with an unknown plugin ID.
   */
  public function testInvalidPluginId(): void {
    $this->entity->set('plugin', 'non_existent');
    $this->assertValidationErrors(["The 'non_existent' plugin does not exist."]);
  }

  /**
   * Tests that the block entity's ID is validated as a machine name.
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
