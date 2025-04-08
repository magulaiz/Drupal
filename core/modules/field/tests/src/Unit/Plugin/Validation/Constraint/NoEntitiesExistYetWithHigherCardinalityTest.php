<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Unit\Plugin\Validation\Constraint;

use Drupal\field\Plugin\Validation\Constraint\NoEntitiesExistYetWithHigherCardinality;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Validator\Exception\MissingOptionsException as MissingOptionsExceptionAlias;

/**
 * Tests the NoEntitiesExistYetWithHigherCardinality constraint.
 *
 * @group field
 */
class NoEntitiesExistYetWithHigherCardinalityTest extends UnitTestCase {

  /**
   * Tests the constraint's required options.
   */
  public function testRequiredOptions(): void {
    $options = [
      'entityType' => 'node',
      'fieldName' => 'field_test',
    ];
    $constraint = new NoEntitiesExistYetWithHigherCardinality($options);
    $requiredOptions = $constraint->getRequiredOptions();

    $this->assertTrue(is_array($requiredOptions));
    $this->assertEquals(['entityType', 'fieldName'], $requiredOptions);
  }

  /**
   * Tests the constraint initialization with valid options.
   */
  public function testValidOptions(): void {
    $options = [
      'entityType' => 'node',
      'fieldName' => 'field_test',
    ];

    $constraint = new NoEntitiesExistYetWithHigherCardinality($options);

    $this->assertEquals('node', $constraint->entityType);
    $this->assertEquals('field_test', $constraint->fieldName);
    $this->assertEquals(
      "The field '@field_name' of entity type '@entity_type' has more entries (@max_delta) than the cardinality (@cardinality) allows.",
      $constraint->message
    );
  }

  /**
   * Tests the constraint initialization with missing required options.
   */
  public function testMissingOptions(): void {
    $this->expectException(MissingOptionsExceptionAlias::class);
    $this->expectExceptionMessage('The options "entityType" must be set for constraint');

    new NoEntitiesExistYetWithHigherCardinality(['fieldName' => 'field_test']);
  }

  /**
   * Tests the constraint's default configuration.
   */
  public function testDefaultConfiguration(): void {
    $options = [
      'entityType' => 'user',
      'fieldName' => 'field_example',
    ];

    $constraint = new NoEntitiesExistYetWithHigherCardinality($options);

    $defaultConfig = $constraint->getDefaultOption();
    $this->assertNull($defaultConfig);
  }

  /**
   * Tests the message template with different parameters.
   *
   * @dataProvider messageParametersProvider
   */
  public function testMessageParameters(string $entityType, string $fieldName, int $maxDelta, int $cardinality, string $expectedMessage): void {
    $options = [
      'entityType' => $entityType,
      'fieldName' => $fieldName,
    ];

    $constraint = new NoEntitiesExistYetWithHigherCardinality($options);

    // Simulate the violation building process.
    $parameters = [
      '@field_name' => $fieldName,
      '@entity_type' => $entityType,
      '@max_delta' => (string) $maxDelta,
      '@cardinality' => (string) $cardinality,
    ];

    $message = strtr($constraint->message, $parameters);
    $this->assertEquals($expectedMessage, $message);
  }

  /**
   * Data provider for testMessageParameters.
   */
  public static function messageParametersProvider(): array {
    return [
      [
        'node',
        'field_body',
        3,
        2,
        "The field 'field_body' of entity type 'node' has more entries (3) than the cardinality (2) allows.",
      ],
      [
        'user',
        'field_address',
        5,
        1,
        "The field 'field_address' of entity type 'user' has more entries (5) than the cardinality (1) allows.",
      ],
    ];
  }

}
