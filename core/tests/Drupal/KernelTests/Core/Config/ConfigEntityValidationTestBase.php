<?php

namespace Drupal\KernelTests\Core\Config;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Base class for testing validation of config entities.
 *
 * @group config
 * @group Validation
 */
abstract class ConfigEntityValidationTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * The config entity being tested.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface
   */
  protected ConfigEntityInterface $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');

    // Install Stark so we can add a legitimately installed theme to config
    // dependencies.
    $this->container->get('theme_installer')->install(['stark']);
    $this->container = $this->container->get('kernel')->getContainer();
  }

  /**
   * Ensures that the entity created in ::setUp() has no validation errors.
   */
  public function testEntityIsValid(): void {
    $this->assertInstanceOf(ConfigEntityInterface::class, $this->entity);
    $this->assertValidationErrors([]);
  }

  /**
   * Returns the validation constraints applied to the entity's ID.
   *
   * If the entity type does not define an ID key, the test will fail. If an ID
   * key is defined but is not using the `machine_name` data type, the test will
   * be skipped.
   *
   * @return array[]
   *   The validation constraint configuration applied to the entity's ID.
   */
  protected function getMachineNameConstraints(): array {
    $id_key = $this->entity->getEntityType()->getKey('id');
    $this->assertNotEmpty($id_key, "The entity under test does not define an ID key.");

    $data_definition = $this->entity->getTypedData()
      ->get($id_key)
      ->getDataDefinition();
    if ($data_definition->getDataType() === 'machine_name') {
      return $data_definition->getConstraints();
    }
    else {
      $this->markTestSkipped("The entity's ID key does not use the machine_name data type.");
    }
  }

  /**
   * Data provider for ::testInvalidMachineNameCharacters().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerInvalidMachineNameCharacters(): array {
    return [
      'space separated' => ['space separated'],
      'dash separated' => ['dash-separated'],
      'underscore separated' => ['underscore_separated'],
      'uppercase letters' => ['Uppercase_Letters'],
    ];
  }

  /**
   * Tests that the entity's ID is tested for invalid characters.
   *
   * @param string $machine_name
   *   A machine name to test.
   *
   * @dataProvider providerInvalidMachineNameCharacters
   */
  public function testInvalidMachineNameCharacters(string $machine_name): void {
    $constraints = $this->getMachineNameConstraints();

    $this->assertNotEmpty($constraints['Regex']);
    $this->assertIsString($constraints['Regex']);

    if (preg_match($constraints['Regex'], $machine_name)) {
      $expected_errors = [];
    }
    else {
      $expected_errors = ['This value is not valid.'];
    }

    $this->entity->set(
      $this->entity->getEntityType()->getKey('id'),
      $machine_name
    );
    $this->assertValidationErrors($expected_errors);
  }

  /**
   * Tests that the entity ID's length is validated if it is a machine name.
   */
  public function testMachineNameLength(): void {
    $constraints = $this->getMachineNameConstraints();

    $max_length = $constraints['Length']['max'];
    $this->assertIsInt($max_length);
    $this->assertGreaterThan(0, $max_length);

    $this->entity->set(
      $this->entity->getEntityType()->getKey('id'),
      mb_strtolower($this->randomMachineName($max_length + 2))
    );
    $this->assertValidationErrors([
      'This value is too long. It should have <em class="placeholder">' . $max_length . '</em> characters or less.',
    ]);
  }

  /**
   * Data provider for ::testConfigDependenciesValidation().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerConfigDependenciesValidation(): array {
    return [
      'valid dependency types' => [
        [
          'config' => ['system.site'],
          'content' => ['node:some-random-uuid'],
          'module' => ['system'],
          'theme' => ['stark'],
        ],
        [],
      ],
      'unknown dependency type' => [
        [
          'fun_stuff' => ['star-trek.deep-space-nine'],
        ],
        [
          "'fun_stuff' is not a supported key.",
        ],
      ],
      'empty string in config dependencies' => [
        [
          'config' => [''],
        ],
        [
          'This value should not be blank.',
          "The '' config does not exist.",
        ],
      ],
      'non-existent config dependency' => [
        [
          'config' => ['fake_settings'],
        ],
        [
          "The 'fake_settings' config does not exist.",
        ],
      ],
      'empty string in module dependencies' => [
        [
          'module' => [''],
        ],
        [
          'This value should not be blank.',
          "Module '' is not installed.",
        ],
      ],
      'invalid module dependency' => [
        [
          'module' => ['invalid-module-name'],
        ],
        [
          'This value is not valid.',
          "Module 'invalid-module-name' is not installed.",
        ],
      ],
      'non-installed module dependency' => [
        [
          'module' => ['bad_judgment'],
        ],
        [
          "Module 'bad_judgment' is not installed.",
        ],
      ],
      'empty string in theme dependencies' => [
        [
          'theme' => [''],
        ],
        [
          'This value should not be blank.',
          "Theme '' is not installed.",
        ],
      ],
      'invalid theme dependency' => [
        [
          'theme' => ['invalid-theme-name'],
        ],
        [
          'This value is not valid.',
          "Theme 'invalid-theme-name' is not installed.",
        ],
      ],
      'non-installed theme dependency' => [
        [
          'theme' => ['ugly_theme'],
        ],
        [
          "Theme 'ugly_theme' is not installed.",
        ],
      ],
    ];
  }

  /**
   * Tests validation of config dependencies.
   *
   * @param array[] $dependencies
   *   The dependencies that should be added to the config entity under test.
   * @param string[] $expected_messages
   *   The expected constraint violation messages.
   *
   * @dataProvider providerConfigDependenciesValidation
   */
  public function testConfigDependenciesValidation(array $dependencies, array $expected_messages): void {
    // Add the dependencies we were given to the dependencies that may already
    // exist in the entity.
    $dependencies = NestedArray::mergeDeep($this->entity->getDependencies(), $dependencies);

    $this->entity->set('dependencies', $dependencies);
    $this->assertValidationErrors($expected_messages);

    // Enforce these dependencies, and ensure we get the same results.
    $this->entity->set('dependencies', [
      'enforced' => $dependencies,
    ]);
    $this->assertValidationErrors($expected_messages);
  }

  /**
   * Asserts a set of validation errors is raised when the entity is validated.
   *
   * @param string[] $expected_messages
   *   The expected validation error messages.
   */
  protected function assertValidationErrors(array $expected_messages): void {
    /** @var \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data */
    $typed_data = $this->container->get('typed_data_manager');
    $definition = $typed_data->createDataDefinition('entity:' . $this->entity->getEntityTypeId());
    $violations = $typed_data->create($definition, $this->entity)->validate();

    $actual_messages = [];
    foreach ($violations as $violation) {
      $actual_messages[] = (string) $violation->getMessage();
    }
    $this->assertSame($expected_messages, $actual_messages);
  }

}
