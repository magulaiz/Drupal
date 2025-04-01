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

      public function returnTrue(): bool {
        return TRUE;
      }

      public function returnFalse(): bool {
        return FALSE;
      }

      public function returnNotTrue(): string {
        return 'true';
      }

    });

  }

  /**
   * Data provider for service validation test cases.
   */
  public static function provideServiceValidationCases(): array {
    return [
      'false result' => [
        'method' => 'returnFalse',
        'expected_violations' => 1,
        'message' => 'Validation failed when returning FALSE.',
        'expected_violation_message' => 'Calling \'returnFalse\' method with value \'1\' on \'test.service\' evaluated as invalid.',
      ],
      'true result' => [
        'method' => 'returnTrue',
        'expected_violations' => 0,
        'message' => 'Validation succeeds when returning TRUE.',
      ],
      'truthy result' => [
        'method' => 'returnNotTrue',
        'expected_violations' => 1,
        'message' => 'Validation fails when returning \'true\'.',
        'expected_violation_message' => 'Calling \'returnNotTrue\' method with value \'1\' on \'test.service\' evaluated as invalid.',
      ],
    ];
  }

  /**
   * @dataProvider provideServiceValidationCases
   */
  public function testValidationForService(string $method, int $expected_violations, string $message, ?string $expected_violation_message = NULL): void {
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['classOrService' => 'test.service', 'method' => $method]);
    $typed_data = $this->typedData->create($definition, 1);
    $violations = $typed_data->validate();
    $this->assertEquals($expected_violations, $violations->count(), $message);
    if ($expected_violation_message) {
      $this->assertEquals($expected_violation_message, $violations->get(0)->getMessage());
    }
  }

  public function testNonExistingMethod(): void {
    // Test with a non-existing method.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['classOrService' => 'test.service', 'method' => 'missingMethod']);
    $typed_data = $this->typedData->create($definition, 1);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The method "missingMethod" does not exist on the service "test.service".');
    $typed_data->validate();
  }

  public function testNonExistingService(): void {
    // Test with a non-existing service.
    $definition = DataDefinition::create('integer')
      ->addConstraint('ClassResolver', ['classOrService' => '\Drupal\NonExisting\Class', 'method' => 'boo']);
    $typed_data = $this->typedData->create($definition, 1);

    $this->expectExceptionMessage('Class "\Drupal\NonExisting\Class" does not exist.');
    $typed_data->validate();
  }

}
