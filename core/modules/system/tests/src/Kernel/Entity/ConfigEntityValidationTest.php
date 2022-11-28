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
    $data['dependencies']['theme'][] = 'stark';
    $data['dependencies']['module'][] = 'system';
    $data['dependencies']['config'][] = 'user.settings';
    $data['dependencies']['content'][] = 'node:some-random-uuid';
    $this->assertCount(0, $typed_config->createFromNameAndData($name, $data)->validate());

    // Adding an unrecognized dependency type should raise an error.
    $data['dependencies']['fun_stuff'][] = 'star-trek.deep-space-nine';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'fun_stuff' is not a supported key.", (string) $violations->get(0)->getMessage());

    // Adding a dependency on non-existent config should raise an error.
    unset($data['dependencies']['fun_stuff']);
    $data['dependencies']['config'][] = 'node.settings';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'node.settings' config does not exist.", (string) $violations->get(0)->getMessage());

    // Trying to put an empty string in config dependencies should also raise an
    // error.
    array_splice($data['dependencies']['config'], -1, NULL, ['']);
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(2, $violations);
    $this->assertSame('This value should not be blank.', (string) $violations->get(0)->getMessage());
    $this->assertSame("The '' config does not exist.", (string) $violations->get(1)->getMessage());
  }

}
