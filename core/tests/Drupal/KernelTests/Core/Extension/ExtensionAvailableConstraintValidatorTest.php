<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Extension;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the ExtensionAvailable constraint validator.
 *
 * @group Validation
 *
 * @covers \Drupal\Core\Extension\Plugin\Validation\Constraint\ExtensionAvailableConstraint
 * @covers \Drupal\Core\Extension\Plugin\Validation\Constraint\ExtensionAvailableConstraintValidator
 */
class ExtensionAvailableConstraintValidatorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * Tests the ExtensionAvailable constraint validator.
   */
  public function testValidation(): void {
    // Create a data definition that specifies the value must be a string with
    // the name of an installed module.
    $definition = DataDefinition::create('string')
      ->addConstraint('ExtensionAvailable', 'module');

    /** @var \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data */
    $typed_data = $this->container->get('typed_data_manager');

    $definition->setConstraints(['ExtensionAvailable' => 'profile']);
    $data = $typed_data->create($definition, 'minimal');

    // Assuming 'minimal' profile is installed.
    $violations = $data->validate();
    $this->assertCount(0, $violations);

    // Check an uninstalled profile by setting a fake profile name.
    $data->setValue('fake_profile');
    $violations = $data->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("Profile 'fake_profile' does not exist.", (string) $violations->get(0)->getMessage());

    // NULL should not trigger a validation error: a value may be nullable.
    $data->setValue(NULL);
    $this->assertCount(0, $data->validate());
  }

}
