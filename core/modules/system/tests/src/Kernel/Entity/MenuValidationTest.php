<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\system\Entity\Menu;

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
      'id' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the menu entity's ID is validated as a machine name.
   */
  public function testMachineName(): void {
    // The entity should be valid to begin with.
    $this->assertValidationErrors([]);

    $this->entity->set('id', 'invalid_name');
    $this->assertValidationErrors(['This value is not valid.']);

    $this->entity->set('id', mb_strtolower($this->randomMachineName(34)));
    $this->assertValidationErrors(['This value is too long. It should have <em class="placeholder">32</em> characters or less.']);
  }

}
