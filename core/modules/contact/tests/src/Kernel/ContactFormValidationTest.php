<?php

namespace Drupal\Tests\contact\Kernel;

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of contact_form entities.
 *
 * @group contact
 */
class ContactFormValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['contact', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = ContactForm::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the content form's ID is validated as a machine name.
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

    $max_length = EntityTypeInterface::BUNDLE_MAX_LENGTH;
    $this->entity->set('id', mb_strtolower($this->randomMachineName($max_length + 2)));
    $this->assertValidationErrors(['This value is too long. It should have <em class="placeholder">' . $max_length . '</em> characters or less.']);
  }

}
