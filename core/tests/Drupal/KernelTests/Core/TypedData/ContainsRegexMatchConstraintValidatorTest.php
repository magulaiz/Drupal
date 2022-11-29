<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Tests the ContainsRegexMatch validation constraint.
 *
 * @group Validation
 *
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ContainsRegexMatchConstraint
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ContainsRegexMatchConstraintValidator
 */
class ContainsRegexMatchConstraintValidatorTest extends KernelTestBase {

  /**
   * Tests the ContainsRegexMatch validation constraint validator.
   */
  public function testValidation(): void {
    // Create a data definition that specifies certain allowed keys.
    $definition = DataDefinition::create('any')
      ->addConstraint('ContainsRegexMatch', '/^Hello/');

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

    // Empty arrays should raise an error.
    $values = [];
    $violations = $typed_data->create($definition, $values)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('Does not contain a value matching "/^Hello/".', (string) $violations->get(0)->getMessage());

    // An array with no matches should raise an error.
    array_push($values, 'Hola!', 'Bonjour!');
    $violations = $typed_data->create($definition, $values)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('Does not contain a value matching "/^Hello/".', (string) $violations->get(0)->getMessage());

    // If we add a matching value, there should be no errors.
    $values[] = 'Hello!';
    $this->assertCount(0, $typed_data->create($definition, $values)->validate());

    // An invalid regular expression should raise a warning, and we will also
    // throw an exception.
    $this->expectWarning();
    try {
      $definition = DataDefinition::create('any')
        ->addConstraint('ContainsRegexMatch', '/^Hello');

      $typed_data->create($definition, $values)->validate();
      $this->fail('Expected an exception but none was raised.');
    }
    catch (LogicException $e) {
      $this->assertSame("Invalid regular expression: '/^Hello'", $e->getMessage());
    }
  }

}
