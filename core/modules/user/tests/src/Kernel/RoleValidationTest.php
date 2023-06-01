<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests validation of user_role entities.
 *
 * @group user
 */
class RoleValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = Role::create([
      'id' => 'test',
      'label' => 'Test',
      'weight' => 0,
      'is_admin' => FALSE,
    ]);
    $this->entity->save();
  }

}
