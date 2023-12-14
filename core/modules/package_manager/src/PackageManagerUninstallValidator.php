<?php

declare(strict_types = 1);

namespace Drupal\package_manager;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use PhpTuf\ComposerStager\API\Core\BeginnerInterface;
use PhpTuf\ComposerStager\API\Core\CommitterInterface;
use PhpTuf\ComposerStager\API\Core\StagerInterface;
use PhpTuf\ComposerStager\API\Path\Factory\PathFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Prevents any module from being uninstalled if update is in process.
 *
 * @internal
 *   This is an internal part of Package Manager and may be changed or removed
 *   at any time without warning. External code should not interact with this
 *   class.
 */
final class PackageManagerUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new PackageManagerUninstallValidator object.
   */
  public function __construct(private PathLocator $pathLocator, private BeginnerInterface $beginner, private StagerInterface $stager, private CommitterInterface $committer, private QueueFactory $queueFactory, private EventDispatcherInterface $eventDispatcher, private SharedTempStoreFactory $sharedTempStoreFactory, private TimeInterface $time, private PathFactoryInterface $pathFactory, private FailureMarker $failureMarker) {}

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $stage = new class(
      $this->pathLocator,
      $this->beginner,
      $this->stager,
      $this->committer,
      $this->queueFactory,
      $this->eventDispatcher,
      $this->sharedTempStoreFactory,
      $this->time,
      $this->pathFactory,
      $this->failureMarker) extends StageBase {};
    if ($stage->isAvailable() || !$stage->isApplying()) {
      return [];
    }
    if ($stage->isApplying()) {
      $reasons[] = $this->t('Modules cannot be uninstalled while Package Manager is applying staged changes to the active code base.');
    }
    return $reasons;
  }

}
