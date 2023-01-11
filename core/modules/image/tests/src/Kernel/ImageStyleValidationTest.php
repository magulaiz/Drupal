<?php

namespace Drupal\Tests\image\Kernel;

use Drupal\image\Entity\ImageStyle;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of image_style entities.
 *
 * @group image
 */
class ImageStyleValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['image'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = ImageStyle::create([
      'name' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the action entity's ID is validated as a machine name.
   *
   * @param string $invalid_id
   *   An invalid machine name that should raise a validation error.
   *
   * @testWith ["invalid name"]
   *  ["invalid-name"]
   *  ["Invalid_Name"]
   */
  public function testMachineName(string $invalid_id): void {
    $this->entity->set('name', $invalid_id);
    $this->assertValidationErrors(['This value is not valid.']);

    $this->entity->set('name', mb_strtolower($this->randomMachineName(68)));
    $this->assertValidationErrors(['This value is too long. It should have <em class="placeholder">64</em> characters or less.']);
  }

}
