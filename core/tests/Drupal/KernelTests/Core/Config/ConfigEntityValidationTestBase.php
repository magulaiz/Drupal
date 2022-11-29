<?php

namespace Drupal\KernelTests\Core\Config;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
  }

  /**
   * Data provider for ::testConfigDependenciesValidation().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerConfigDependenciesValidation(): array {
    return [
      'additional valid dependency types' => [
        [
          'config' => ['system.site'],
          'content' => ['node:some-random-uuid'],
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
      'non-installed module dependency' => [
        [
          'module' => ['node'],
        ],
        [
          "Module 'node' is not installed.",
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
      'non-installed theme dependency' => [
        [
          'theme' => ['stark'],
        ],
        [
          "Theme 'stark' is not installed.",
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
    $this->assertInstanceOf(ConfigEntityInterface::class, $this->entity);

    // The entity should have valid data to begin with.
    $this->assertCount(0, $this->validateEntity());

    $this->entity->set('dependencies', NestedArray::mergeDeep($this->entity->getDependencies(), $dependencies));
    $violations = $this->validateEntity();

    $this->assertSame(count($expected_messages), count($violations));
    foreach ($expected_messages as $i => $message) {
      $this->assertSame($message, (string) $violations->get($i)->getMessage());
    }
  }

  /**
   * Validates the entity under test.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   A list of validation errors.
   */
  protected function validateEntity(): ConstraintViolationListInterface {
    return $this->container->get('config.typed')
      ->createFromNameAndData($this->entity->getConfigDependencyName(), $this->entity->toArray())
      ->validate();
  }

}
