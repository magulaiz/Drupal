<?php

declare(strict_types = 1);

namespace Drupal\Tests\auto_updates\Kernel\StatusCheck;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\package_manager\Event\PreApplyEvent;
use Drupal\Tests\auto_updates\Kernel\AutoUpdatesKernelTestBase;
use ColinODell\PsrTestLogger\TestLogger;

/**
 * @covers \Drupal\auto_updates\Validator\StagedDatabaseUpdateValidator
 * @group auto_updates
 * @internal
 */
class StagedDatabaseUpdateValidatorTest extends AutoUpdatesKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['auto_updates'];

  /**
   * Tests that unattended updates are stopped by staged database updates.
   */
  public function testStagedDatabaseUpdateExists(): void {
    $logger = new TestLogger();
    $this->container->get('logger.channel.auto_updates')
      ->addLogger($logger);

    $this->getStageFixtureManipulator()->setCorePackageVersion('9.8.1');

    $listener = function (PreApplyEvent $event): void {
      $dir = $event->stage->getStageDirectory() . '/core/modules/system';
      mkdir($dir, 0777, TRUE);
      file_put_contents($dir . '/system.install', "<?php\nfunction system_update_10101010() {}");
    };
    $this->addEventTestListener($listener);

    $this->runConsoleUpdateStage();
    $expected_message = "The update cannot proceed because database updates have been detected in the following extensions.\nSystem\n";
    $this->assertTrue($logger->hasRecord($expected_message, (string) RfcLogLevel::ERROR));
  }

}
