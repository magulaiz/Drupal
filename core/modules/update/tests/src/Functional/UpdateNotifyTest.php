<?php

namespace Drupal\Tests\update\Functional;

use Drupal\Core\Datetime\DateHelper;

/**
 * Tests that the update notification email is sent.
 *
 * @group update
 */
class UpdateNotifyTest extends UpdateTestBase {

  use UpdateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update_test', 'update'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Sets the version to x.x.x when no project-specific mapping is defined.
   *
   * @param string $version
   *   The version.
   */
  protected function setProjectInstalledVersion($version) {
    $this->mockDefaultExtensionsInfo(['version' => $version]);
  }

  /**
   * Checks that the update notification is sent on configured day.
   */
  public function testUpdateNotificationSent() {
    $this->setProjectInstalledVersion('8.0.0');
    $this->mockReleaseHistory(['drupal' => '0.1']);

    $request_time = \Drupal::time()->getRequestTime();
    $day = (int) DateHelper::dayOfWeek(date('Y-m-d', $request_time));
    $update_settings = \Drupal::configFactory()->getEditable('update.settings');
    $update_settings
      ->set('check.interval_days', 7)
      ->set('check.update_day', $day)
      ->set('notification.emails', ['abc@gmail.com'])
      ->save(TRUE);
    $cron = $this->container->get('cron');
    $cron->run();
    // Last email notification time will be set to the recent request time if
    // the mail has been sent. @see _update_cron_notify().
    $this->assertSame($request_time, \Drupal::state()->get('update.last_email_notification'));
  }

}
