<?php

namespace Drupal\Tests\shortcut\Kernel;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\shortcut\Entity\ShortcutSet;

/**
 * Tests validation of shortcut_set entities.
 *
 * @group shortcut
 */
class ShortcutSetValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link', 'shortcut'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('shortcut');
    $this->installEntitySchema('shortcut');

    $this->entity = ShortcutSet::create([
      'id' => 'test-shortcut-set',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the shortcut set's ID is validated as a machine name.
   *
   * @param string $invalid_id
   *   An invalid machine name that should raise a validation error.
   *
   * @testWith ["invalid name"]
   *  ["invalid_name"]
   *  ["Invalid-Name"]
   */
  public function testMachineName(string $invalid_id): void {
    $this->entity->set('id', $invalid_id);
    $this->assertValidationErrors(['This value is not valid.']);
  }

}
