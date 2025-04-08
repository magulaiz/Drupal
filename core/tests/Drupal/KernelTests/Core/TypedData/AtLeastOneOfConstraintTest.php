<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests AtLeastOneOf validation constraint with both valid and invalid values.
 *
 * @group Validation
 */
class AtLeastOneOfConstraintTest extends KernelTestBase {

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedData;


  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->typedData = $this->container->get('typed_data_manager');
  }

  /**
   * Tests the AllowedValues validation constraint validator.
   *
   * For testing we define an integer with a set of allowed values.
   *
   * @dataProvider dataProvider
   */
  public function testValidation($type, $value, $constraints, $expectedViolations): void {
    // Create a definition that specifies some AllowedValues.
    $definition = DataDefinition::create($type)
      ->addConstraint('AtLeastOneOf', [
        'constraints' => $constraints,
      ]);

    // Test the validation.
    $typed_data = $this->typedData->create($definition, $value);
    $violations = $typed_data->validate();

    $violationMessages = [];
    foreach ($violations as $violation) {
      $violationMessages[] = $violation->getMessage();
    }

    $this->assertEquals($expectedViolations, $violationMessages, 'Validation passed for correct value.');
  }


  public static function dataProvider() {
    return [
      [
        'integer',
        1,
        [
          ['Range' => ['min' => 100]],
          ['NotNull' => []],
        ],
        [],
      ],
      [
        'integer',
        250,
        [
          ['Range' => ['min' => 100]],
          ['AllowedValues' => [500]],
        ],
        [],
      ],
      [
        'string',
        'Green',
        [
          ['AllowedValues' => ['test']],
          ['Blank' => []],
        ],
        [
          'This value should satisfy at least one of the following constraints: [1] The value you selected is not a valid choice. [2] This value should be blank.',
        ],
      ]
    ];
  }
}
