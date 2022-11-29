<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Symfony validation of config entities.
 *
 * @group Entity
 * @group Validation
 *
 * @covers \Drupal\Core\Entity\Plugin\Validation\Constraint\ConfigDependenciesConstraint
 * @covers \Drupal\Core\Entity\Plugin\Validation\Constraint\ConfigDependenciesConstraintValidator
 */
class ConfigEntityValidationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user'];

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
          'config' => ['user.settings'],
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
      'non-existent config dependency' => [
        [
          'config' => ['node.settings'],
        ],
        [
          "The 'node.settings' config does not exist.",
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
    $this->installConfig(['system', 'user']);

    /** @var \Drupal\Core\Entity\EntityViewModeInterface $entity */
    $entity = EntityViewMode::create([
      'id' => 'user.test',
      'label' => 'Test',
      'targetEntityType' => 'user',
    ]);
    $entity->save();

    $name = $entity->getConfigDependencyName();
    $data = $entity->toArray();

    /** @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config */
    $typed_config = $this->container->get('config.typed');

    // The entity should have valid data to begin with.
    $this->assertCount(0, $typed_config->createFromNameAndData($name, $data)->validate());

    $data['dependencies'] = NestedArray::mergeDeep($data['dependencies'], $dependencies);
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();

    $this->assertSame(count($expected_messages), count($violations));
    foreach ($expected_messages as $i => $message) {
      $this->assertSame($message, (string) $violations->get($i)->getMessage());
    }
  }

}
