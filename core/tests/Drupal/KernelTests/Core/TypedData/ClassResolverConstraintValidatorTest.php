<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests ClassResolver validation constraint with both valid and invalid values.
 *
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ClassResolverConstraintValidator
 * @group Validation
 */
class ClassResolverConstraintValidatorTest extends KernelTestBase {

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected TypedDataManagerInterface $typedData;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->typedData = $this->container->get('typed_data_manager');

    $this->container->set('test.service', new class() {
      public function returnTrue() {
        return true;
      }

      public function returnFalse() {
        return false;
      }
    });

  }

  public function testValidation(): void {
    # Test with a valid value.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['test.service', 'returnFalse']);
    $typed_data = $this->typedData->create($definition, 1);
    $violations = $typed_data->validate();
    $this->assertEquals(1, $violations->count(), 'Validation failed when returning TRUE.');

    # Test with an invalid value.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['test.service', 'returnTrue']);
    $typed_data = $this->typedData->create($definition, 1);
    $violations = $typed_data->validate();
    $this->assertEquals(0, $violations->count(), 'Validation succeeds when returning TRUE.');
  }

  public function testNonExistingMethod(): void {    # Test with a non-existing method.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['test.service', 'missingMethod']);
    $typed_data = $this->typedData->create($definition, 1);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The method "missingMethod" does not exist on the service "test.service".');
    $typed_data->validate();
  }

  public function testNonExistingService(): void {
    # Test with a non-existing service.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['phantom.service', 'boo']);
    $typed_data = $this->typedData->create($definition, 1);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('You have requested a non-existent service "phantom.service".');
    $typed_data->validate();
  }

}
