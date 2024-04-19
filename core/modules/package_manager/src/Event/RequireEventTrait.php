<?php

declare(strict_types=1);

namespace Drupal\package_manager\Event;

use Drupal\package_manager\StageBase;

/**
 * Common methods for pre- and post-require events.
 *
 * @internal
 *   This is an internal part of Automatic Updates and should only be used by
 *   \Drupal\package_manager\Event\PreRequireEvent and
 *   \Drupal\package_manager\Event\PostRequireEvent.
 */
trait RequireEventTrait {

  /**
   * Constructs the object.
   *
   * @param \Drupal\package_manager\StageBase $stage
   *   The stage.
   * @param string[] $runtimePackages
   *   The runtime (i.e., non-dev) packages to be required, in the form
   *   'vendor/name:constraint'.
   * @param string[] $devPackages
   *   The dev packages to be required, in the form 'vendor/name:constraint'.
   */
  public function __construct(StageBase $stage, private readonly array $runtimePackages, private readonly array $devPackages = []) {
    parent::__construct($stage);
  }

  /**
   * Gets the runtime (i.e., non-dev) packages.
   *
   * @return string[]
   *   An array of packages where the values are version constraints and keys
   *   are package names in the form `vendor/name`. Packages without a version
   *   constraint will default to `*`.
   */
  public function getRuntimePackages(): array {
    return $this->getKeyedPackages($this->runtimePackages);
  }

  /**
   * Gets the dev packages.
   *
   * @return string[]
   *   An array of packages where the values are version constraints and keys
   *   are package names in the form `vendor/name`. Packages without a version
   *   constraint will default to `*`.
   */
  public function getDevPackages(): array {
    return $this->getKeyedPackages($this->devPackages);
  }

  /**
   * Gets packages as a keyed array.
   *
   * @param string[] $packages
   *   The packages, in the form 'vendor/name:version'.
   *
   * @return string[]
   *   An array of packages where the values are version constraints and keys
   *   are package names in the form `vendor/name`. Packages without a version
   *   constraint will default to `*`.
   */
  private function getKeyedPackages(array $packages): array {
    $keyed_packages = [];
    foreach ($packages as $package) {
      if (strpos($package, ':') > 0) {
        [$name, $constraint] = explode(':', $package);
      }
      else {
        [$name, $constraint] = [$package, '*'];
      }
      $keyed_packages[$name] = $constraint;
    }
    return $keyed_packages;
  }

}
