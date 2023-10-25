<?php

declare(strict_types = 1);

namespace Drupal\Tests\auto_updates\Kernel\StatusCheck;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\auto_updates\CronUpdateRunner;
use Drupal\auto_updates\Validator\WindowsValidator;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\package_manager\ValidationResult;
use Drupal\Tests\auto_updates\Kernel\AutoUpdatesKernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * @covers \Drupal\auto_updates\Validator\WindowsValidator
 * @group auto_updates
 * @internal
 */
class WindowsValidatorTest extends AutoUpdatesKernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['auto_updates', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('auto_updates');
  }

  /**
   * Data provider for ::testBackgroundUpdatesDisallowedOnWindows().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerBackgroundUpdatesDisallowedOnWindows(): array {
    return [
      'updates enabled via web, user has access to update form' => [
        ['administer software updates'],
        [
          'method' => 'web',
          'level' => CronUpdateRunner::ALL,
        ],
        [
          ValidationResult::createError([
            t('Unattended updates are not supported on Windows. Use <a href="/admin/reports/updates/update">the update form</a> to update Drupal core.'),
          ]),
        ],
      ],
      'updates enabled via web, user cannot access update form' => [
        [],
        [
          'method' => 'web',
          'level' => CronUpdateRunner::ALL,
        ],
        [
          ValidationResult::createError([
            t('Unattended updates are not supported on Windows.'),
          ]),
        ],
      ],
      'updates enabled via console, user has access to update form' => [
        ['administer software updates'],
        [
          'method' => 'console',
          'level' => CronUpdateRunner::ALL,
        ],
        [],
      ],
      'updates enabled via console, user cannot access update form' => [
        [],
        [
          'method' => 'console',
          'level' => CronUpdateRunner::ALL,
        ],
        [],
      ],
      'updates disabled, user has access to update form' => [
        ['administer software updates'],
        [
          'method' => 'web',
          'level' => CronUpdateRunner::DISABLED,
        ],
        [],
      ],
      'updates disabled, user cannot access update form' => [
        [],
        [
          'method' => 'web',
          'level' => CronUpdateRunner::DISABLED,
        ],
        [],
      ],
    ];
  }

  /**
   * Tests that background updates are not allowed on Windows.
   *
   * @param array $user_permissions
   *   The permissions the current user should have, if any.
   * @param array $unattended_update_settings
   *   The `auto_updates.settings:unattended` config values.
   * @param \Drupal\package_manager\ValidationResult[] $expected_results
   *   The expected validation results on Windows.
   *
   * @dataProvider providerBackgroundUpdatesDisallowedOnWindows
   */
  public function testBackgroundUpdatesDisallowedOnWindows(array $user_permissions, array $unattended_update_settings, array $expected_results): void {
    if ($user_permissions) {
      $this->setUpCurrentUser([], $user_permissions, FALSE);
    }

    $this->config('auto_updates.settings')
      ->set('unattended', $unattended_update_settings)
      ->save();

    $property = new \ReflectionProperty(WindowsValidator::class, 'os');
    $property->setValue(NULL, 'Windows');
    $this->assertCheckerResultsFromManager($expected_results, TRUE);

    $logger = new TestLogger();
    $this->container->get('logger.factory')
      ->get('auto_updates')
      ->addLogger($logger);
    // If any validation errors are expected, they should be logged if we try to
    // run an unattended update.
    $this->runConsoleUpdateStage();
    foreach ($expected_results as $result) {
      foreach ($result->messages as $message) {
        $this->assertTrue($logger->hasRecordThatContains((string) $message, RfcLogLevel::ERROR));
      }
    }

    // If we're not on Windows, we should never get an error.
    $property->setValue(NULL, 'Linux');
    $this->assertCheckerResultsFromManager([], TRUE);

    // If unattended updates are enabled, ensure that they will succeed.
    if ($unattended_update_settings['level'] !== CronUpdateRunner::DISABLED) {
      $logger->reset();
      $this->getStageFixtureManipulator()->setCorePackageVersion('9.8.1');
      $this->runConsoleUpdateStage();
      $this->assertFalse($logger->hasRecords(RfcLogLevel::ERROR));
    }
  }

}
