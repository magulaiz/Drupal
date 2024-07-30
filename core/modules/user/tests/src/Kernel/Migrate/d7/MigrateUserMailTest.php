<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel\Migrate\d7;

use Drupal\Core\Utility\UserEmailNotification;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

/**
 * Migrates user mail configuration.
 *
 * @group user
 */
class MigrateUserMailTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['user']);
    $this->executeMigration('d7_user_mail');
  }

  /**
   * Tests the migration.
   */
  public function testMigration(): void {
    $config = $this->config('user.mail');
    $this->assertSame('Your account is approved!', $config->get(UserEmailNotification::StatusActivated->value . '.subject'));
    $this->assertSame('Your account was activated, and there was much rejoicing.', $config->get(UserEmailNotification::StatusActivated->value . '.body'));
    $this->assertSame('Fix your password', $config->get(UserEmailNotification::PasswordReset->value . '.subject'));
    $this->assertSame("Nope! You're locked out forever.", $config->get(UserEmailNotification::PasswordReset->value . '.body'));
    $this->assertSame('So long, bub', $config->get(UserEmailNotification::CancelConfirm->value . '.subject'));
    $this->assertSame('The gates of Drupal are closed to you. Now you will work in the salt mines.', $config->get(UserEmailNotification::CancelConfirm->value . '.body'));
    $this->assertSame('Gawd made you an account', $config->get(UserEmailNotification::RegisterAdminCreated->value . '.subject'));
    $this->assertSame('...and it could be taken away.', $config->get(UserEmailNotification::RegisterAdminCreated->value . '.body'));
    $this->assertSame('Welcome!', $config->get(UserEmailNotification::RegisterNoApprovalRequired->value . '.subject'));
    $this->assertSame('You can now log in if you can figure out how to use Drupal!', $config->get(UserEmailNotification::RegisterNoApprovalRequired->value . '.body'));
    $this->assertSame('Soon...', $config->get(UserEmailNotification::RegisterPendingApproval->value . '.subject'));
    $this->assertSame('...you will join our Circle. Let the Drupal flow through you.', $config->get(UserEmailNotification::RegisterPendingApproval->value . '.body'));
    $this->assertSame('BEGONE!', $config->get(UserEmailNotification::StatusBlocked->value . '.subject'));
    $this->assertSame('You no longer please the robot overlords. Go to your room and chill out.', $config->get(UserEmailNotification::StatusBlocked->value . '.body'));
  }

}
