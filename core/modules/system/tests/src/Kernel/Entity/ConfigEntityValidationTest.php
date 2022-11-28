<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Symfony validation of config entities.
 *
 * @group Entity
 */
class ConfigEntityValidationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user'];

  /**
   * Tests validation of config dependencies.
   */
  public function testConfigDependenciesValidation(): void {
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

    // Adding additional supported dependency types should be allowed. For the
    // purposes of this test, these dependencies don't need to actually exist.
    $data['dependencies']['config'][] = 'user.settings';
    $data['dependencies']['content'][] = 'node:some-random-uuid';
    $this->assertCount(0, $typed_config->createFromNameAndData($name, $data)->validate());

    // Adding an unrecognized dependency type should raise an error.
    $data['dependencies']['fun_stuff'][] = 'star-trek.deep-space-nine';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'fun_stuff' is not a supported key.", (string) $violations->get(0)->getMessage());
    unset($data['dependencies']['fun_stuff']);

    // Trying to add an empty string in config dependencies should raise an
    // error.
    $data['dependencies']['config'][] = '';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(2, $violations);
    $this->assertSame('This value should not be blank.', (string) $violations->get(0)->getMessage());
    $this->assertSame("The '' config does not exist.", (string) $violations->get(1)->getMessage());
    array_pop($data['dependencies']['config']);

    // Adding a dependency on non-existent config should raise an error.
    $data['dependencies']['config'][] = 'node.settings';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'node.settings' config does not exist.", (string) $violations->get(0)->getMessage());
    array_pop($data['dependencies']['config']);

    // Trying to add an empty string in module dependencies should raise an
    // error.
    $data['dependencies']['module'][] = '';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(2, $violations);
    $this->assertSame('This value should not be blank.', (string) $violations->get(0)->getMessage());
    $this->assertSame("Module '' is not installed.", (string) $violations->get(1)->getMessage());
    array_pop($data['dependencies']['module']);

    // Adding a dependency on a non-installed module should raise an error.
    $data['dependencies']['module'][] = 'node';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("Module 'node' is not installed.", (string) $violations->get(0)->getMessage());
    array_pop($data['dependencies']['module']);

    // Trying to add an empty string in theme dependencies should raise an
    // error.
    $data['dependencies']['theme'][] = '';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(2, $violations);
    $this->assertSame('This value should not be blank.', (string) $violations->get(0)->getMessage());
    $this->assertSame("Theme '' is not installed.", (string) $violations->get(1)->getMessage());
    array_pop($data['dependencies']['theme']);

    // Adding a dependency on a non-installed theme should raise an error.
    $data['dependencies']['theme'][] = 'stark';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("Theme 'stark' is not installed.", (string) $violations->get(0)->getMessage());
    array_pop($data['dependencies']['theme']);

    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(0, $violations);
  }

}
