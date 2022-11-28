<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Symfony validation of config entities.
 */
class ConfigEntityValidationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user'];

  /**
   * Tests validation of config dependencies.
   */
  public function testConfigDependenciesValidation(): void {
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
    $data['dependencies']['config'][] = 'system.site';
    $data['dependencies']['content'][] = 'node:some-random-uuid';
    $this->assertCount(0, $typed_config->createFromNameAndData($name, $data)->validate());

    // Adding an unrecognized dependency type should raise an error.
    $data['dependencies']['fun_stuff'][] = 'star-trek.deep-space-nine';
    $violations = $typed_config->createFromNameAndData($name, $data)->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'fun_stuff' is not a supported key.", (string) $violations->get(0)->getMessage());
  }

}
