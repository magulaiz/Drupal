<?php

namespace Drupal\Tests\contact\Kernel;

use Drupal\contact\Entity\ContactForm;
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
      'message' => '',
      'redirect' => '',
    ]);
    $this->entity->save();
  }

}
