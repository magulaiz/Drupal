<?php

namespace Drupal\KernelTests\Core\Session;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Test case for getting all permissions as a super user.
 *
 * @group Session
 */
class SuperUserPermissionsTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected $usesSuperUserAccessPolicy = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Tests that assigning a role grants that role's permissions.
   */
  public function testPermissionChange(): void {
    // Create two accounts to avoid dealing with user 1.
    $account = $this->createUser();
    $this->assertSame('1', $account->id());
    $this->assertTrue($account->hasPermission('administer modules'));
  }

}
