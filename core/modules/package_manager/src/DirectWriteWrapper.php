<?php

declare(strict_types=1);

namespace Drupal\package_manager;

use PhpTuf\ComposerStager\API\Core\BeginnerInterface;
use PhpTuf\ComposerStager\API\Core\CommitterInterface;
use PhpTuf\ComposerStager\API\Path\Value\PathInterface;
use PhpTuf\ComposerStager\API\Path\Value\PathListInterface;
use PhpTuf\ComposerStager\API\Process\Service\OutputCallbackInterface;
use PhpTuf\ComposerStager\API\Process\Service\ProcessInterface;

final class DirectWriteWrapper implements BeginnerInterface, CommitterInterface {

  /**
   * {@inheritdoc}
   */
  public function begin(PathInterface $activeDir, PathInterface $stagingDir, ?PathListInterface $exclusions = NULL, ?OutputCallbackInterface $callback = NULL, int $timeout = ProcessInterface::DEFAULT_TIMEOUT): void {
  }

  /**
   * {@inheritdoc}
   */
  public function commit(PathInterface $stagingDir, PathInterface $activeDir, ?PathListInterface $exclusions = NULL, ?OutputCallbackInterface $callback = NULL, int $timeout = ProcessInterface::DEFAULT_TIMEOUT): void {
  }

}
