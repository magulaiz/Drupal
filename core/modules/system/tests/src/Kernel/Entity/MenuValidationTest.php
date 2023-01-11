<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\system\Entity\Menu;
use Drupal\system\MenuStorage;

/**
 * Tests validation of menu entities.
 *
 * @group system
 */
class MenuValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = Menu::create([
      'id' => 'test-menu',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the menu entity's ID is validated as a machine name.
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

    $max_length = MenuStorage::MAX_ID_LENGTH;
    $this->entity->set('id', mb_strtolower($this->randomMachineName($max_length + 2)));
    $this->assertValidationErrors([
      'This value is too long. It should have <em class="placeholder">' . $max_length . '</em> characters or less.',
    ]);
  }

}
