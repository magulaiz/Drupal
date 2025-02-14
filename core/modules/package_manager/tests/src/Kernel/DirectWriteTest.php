<?php

declare(strict_types=1);

namespace Drupal\Tests\package_manager\Kernel;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Core\Queue\QueueFactory;
use Drupal\package_manager\PathLocator;
use PhpTuf\ComposerStager\API\Core\BeginnerInterface;
use PhpTuf\ComposerStager\API\Core\CommitterInterface;

/**
 * @group package_manager
 */
class DirectWriteTest extends PackageManagerKernelTestBase {

  /**
   * Tests that direct-write does not work if it is globally disabled.
   */
  public function testSiteSandboxedIfDirectWriteGloballyDisabled(): void {
    // Even if we use a stage that supports direct write, it should not be
    // enabled.
    $stage = $this->createStage(DirectWriteTestStage::class);
    $logger = new TestLogger();
    $stage->setLogger($logger);
    $this->assertFalse($stage->isDirectWrite());
    $stage->create();
    $this->assertTrue($stage->stageDirectoryExists());
    $this->assertNotSame(
      $this->container->get(PathLocator::class)->getProjectRoot(),
      $stage->getStageDirectory(),
    );
    $this->assertFalse($logger->hasRecords('info'));
  }

  /**
   * Tests direct-write mode when globally enabled.
   */
  public function testSiteNotSandboxedIfDirectWriteGloballyEnabled(): void {
    $mock_beginner = $this->createMock(BeginnerInterface::class);
    $mock_beginner->expects($this->never())
      ->method('begin')
      ->withAnyParameters();
    $this->container->set(BeginnerInterface::class, $mock_beginner);

    $mock_committer = $this->createMock(CommitterInterface::class);
    $mock_committer->expects($this->never())
      ->method('commit')
      ->withAnyParameters();
    $this->container->set(CommitterInterface::class, $mock_committer);

    $this->setSetting('package_manager_allow_direct_write', TRUE);

    $stage = $this->createStage(DirectWriteTestStage::class);
    $logger = new TestLogger();
    $stage->setLogger($logger);
    $this->assertTrue($stage->isDirectWrite());
    $stage->create();
    // In direct-write mode, the active and stage directories are the same.
    $this->assertTrue($stage->stageDirectoryExists());
    $this->assertSame(
      $this->container->get(PathLocator::class)->getProjectRoot(),
      $stage->getStageDirectory(),
    );
    $stage->apply();
    $stage->postApply();
    // Destroying the stage should not populate the clean-up queue.
    $stage->destroy();
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->container->get(QueueFactory::class)
      ->get('package_manager_cleanup');
    $this->assertSame(0, $queue->numberOfItems());

    $records = $logger->recordsByLevel['info'];
    $this->assertCount(2, $records);
    $this->assertSame('Direct-write is enabled. Skipping sandboxing.', (string) $records[0]['message']);
    $this->assertSame('Direct-write is enabled. Changes have been made to the running code base.', (string) $records[1]['message']);
  }

}
