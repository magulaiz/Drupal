<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Tests ValidKeys validation constraint with both valid and invalid values.
 *
 * @group Validation
 */
class ValidKeysConstraintValidatorTest extends KernelTestBase {

  /**
   * Tests the ValidKeys validation constraint validator.
   */
  public function testValidation(): void {
    // Create a data definition that specifies certain allowed keys.
    $definition = DataDefinition::create('any')
      ->addConstraint('ValidKeys', ['north', 'south', 'west']);

    /** @var \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data */
    $typed_data = $this->container->get('typed_data_manager');

    // Passing a non-array value should raise an exception.
    try {
      $typed_data->create($definition, 2501)->validate();
      $this->fail('Expected an exception but none was raised.');
    }
    catch (UnexpectedTypeException $e) {
      $this->assertSame('Expected argument of type "array", "int" given', $e->getMessage());
    }

    // Indexed arrays are never valid.
    $violations = $typed_data->create($definition, ['north', 'south'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('Numerically indexed arrays are not allowed.', (string) $violations->get(0)->getMessage());

    // Arrays with automatically assigned keys, AND a valid key, should be
    // considered invalid overall.
    $violations = $typed_data->create($definition, ['north', 'south' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'0' is not a supported key.", (string) $violations->get(0)->getMessage());

    // Associative arrays with an invalid key should be invalid.
    $violations = $typed_data->create($definition, ['north' => 'south', 'east' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'east' is not a supported key.", (string) $violations->get(0)->getMessage());

    // If the array only contains the allowed keys, it's fine.
    $value = [
      'north' => 'Boston',
      'south' => 'Atlanta',
      'west' => 'San Francisco',
    ];
    $violations = $typed_data->create($definition, $value)->validate();
    $this->assertCount(0, $violations);
  }

}
