<?php

namespace Drupal\Composer\Plugin\Unpack;

use Composer\Package\PackageInterface;

/**
 * A collection with packages to unpack.
 */
final class UnpackCollection implements \IteratorAggregate {

  /**
   * The queue of packages to unpack.
   *
   * @var \Composer\Package\PackageInterface[]
   */
  private array $packagesToUnpack = [];

  /**
   * The list of packages that have been unpacked.
   *
   * @var array<string, \Composer\Package\PackageInterface>
   */
  private array $unpackedPackages = [];

  /**
   * The dependencies of the packages that have been unpacked.
   *
   * @var array
   */
  private array $allPackageDependencies = [];

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this);
  }

  /**
   * Add a package to the queue of packages to unpack.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to add to the queue.
   */
  public function enqueuePackage(PackageInterface $package): void {
    if (!isset($this->packagesToUnpack[$package->getPrettyName()])) {
      $this->packagesToUnpack[$package->getPrettyName()] = $package;
    }
  }

  /**
   * Get the queue of packages to unpack.
   *
   * @return \Composer\Package\PackageInterface[]
   *   The queue of packages to unpack.
   */
  public function getPackagesQueue(): array {
    return $this->packagesToUnpack;
  }

  /**
   * Add a package to the list of unpacked packages.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package that has been unpacked.
   */
  public function addUnpackedPackage(PackageInterface $package): void {
    $this->unpackedPackages[$package->getPrettyName()] = $package;
  }

  /**
   * Get the list of unpacked packages.
   *
   * @return array
   *   The list of unpacked packages.
   */
  public function getUnpackedPackages(): array {
    return $this->unpackedPackages;
  }

  /**
   * Check if a package has been unpacked or it's queued for unpacking.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to check.
   *
   * @return bool
   *   TRUE if the package has been unpacked.
   */
  public function isUnpacked(PackageInterface $package): bool {
    return isset($this->unpackedPackages[$package->getPrettyName()]);
  }

  /**
   * Add a dependency to the list of dependencies that have been unpacked.
   *
   * @param string $name
   *   The name of the dependency.
   * @param string $version
   *   The version of the dependency.
   */
  public function addPackageDependencies(string $name, string $version): void {
    if ($this->dependencyExists($name)) {
      if (version_compare($this->allPackageDependencies[$name], $version, '<')) {
        $this->allPackageDependencies[$name] = $version;
      }
    }
    else {
      $this->allPackageDependencies[$name] = [
        'name' => $name,
        'version' => $version,
      ];
    }
  }

  /**
   * Pop a dependency from the list of dependencies that have been unpacked.
   *
   * @return array
   *   The dependency in the queue.
   */
  public function popPackageDependencies(): ?array {
    return array_shift($this->allPackageDependencies);
  }

  /**
   * Check if a dependency has been unpacked.
   *
   * In this case, a dependency is defined as being unpacked if it has been
   * added to the list of dependencies that need to be unpacked into the main
   * composer.json.
   *
   * @param string $name
   *   The name of the dependency.
   *
   * @return bool
   *   TRUE if the dependency has been unpacked.
   */
  public function dependencyExists(string $name): bool {
    return isset($this->allPackageDependencies[$name]);
  }

  /**
   * Pop a package from the queue of packages to unpack.
   *
   * @return \Composer\Package\PackageInterface|null
   *   The package in the queue.
   */
  public function popPackageQueue(): ?PackageInterface {
    return array_shift($this->packagesToUnpack);
  }

}
