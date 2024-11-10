<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\Core\Utility\UserEmailNotification;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that user.mail default settings are parsed correctly.
 *
 * @group user
 */
class UserMailDefaultsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['user']);
  }

  /**
   * Tests that each user mail contains blank lines.
   *
   * @dataProvider userMailsProvider
   */
  public function testMailDefaults($key): void {
    $body = $this->config('user.mail')->get("$key.body");
    $this->assertStringContainsString("\n\n", $body);
  }

  /**
   * Data provider for user mail testing.
   *
   * @return array
   *   Array of arrays containing the set of user mail configuration keys.
   */
  public static function userMailsProvider() {
    return [
      [UserEmailNotification::CancelConfirm->value],
      [UserEmailNotification::PasswordReset->value],
      [UserEmailNotification::StatusActivated->value],
      [UserEmailNotification::StatusBlocked->value],
      [UserEmailNotification::StatusCanceled->value],
      [UserEmailNotification::RegisterAdminCreated->value],
      [UserEmailNotification::RegisterNoApprovalRequired->value],
      [UserEmailNotification::RegisterPendingApproval->value],
      [UserEmailNotification::RegisterPendingApprovalAdmin->value],
    ];
  }

}
