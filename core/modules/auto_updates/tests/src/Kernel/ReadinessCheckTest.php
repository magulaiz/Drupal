<?php

declare(strict_types = 1);

namespace Drupal\Tests\auto_updates\Kernel;

use Drupal\auto_updates\Event\ReadinessCheckEvent;
use Drupal\package_manager\StatusCheckTrait;

/**
 * Tests that running readiness checks raises deprecation notices.
 *
 * @group legacy
 * @internal
 */
class ReadinessCheckTest extends AutoUpdatesKernelTestBase {

  use StatusCheckTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['auto_updates'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->addEventTestListener(function () {}, ReadinessCheckEvent::class);
  }

  /**
   * Tests running readiness check via StatusCheckTrait.
   */
  public function testStatusCheckTrait(): void {
    $this->expectDeprecation(ReadinessCheckEvent::class . ' is deprecated in auto_updates:8.x-2.5 and will be removed in auto_updates:3.0.0. Use \Drupal\package_manager\Event\StatusCheckEvent instead. See https://www.drupal.org/node/3316086.');
    $this->runStatusCheck($this->createStage(), $this->container->get('event_dispatcher'), TRUE);
  }

  /**
   * Tests running readiness checks using the readiness validation manager.
   */
  public function testReadinessValidationManager(): void {
    $this->expectDeprecation('The "auto_updates.readiness_validation_manager" service is deprecated in auto_updates:8.x-2.5 and is removed from auto_updates:3.0.0. Use the auto_updates.status_checker service instead. See https://www.drupal.org/node/3316086.');
    $this->expectDeprecation(ReadinessCheckEvent::class . ' is deprecated in auto_updates:8.x-2.5 and will be removed in auto_updates:3.0.0. Use \Drupal\package_manager\Event\StatusCheckEvent instead. See https://www.drupal.org/node/3316086.');
    $this->container->get('auto_updates.readiness_validation_manager')
      ->run();
  }

}
