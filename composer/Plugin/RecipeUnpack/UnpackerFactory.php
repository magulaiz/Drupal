<?php

namespace Drupal\Composer\Plugin\RecipeUnpack;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Drupal\Core\Recipe\Recipe;

/**
 * Unpacker factory for dependency unpackers.
 */
final readonly class UnpackerFactory {

  public function __construct(
    private Composer $composer,
    private IOInterface $io,
    private UnpackCollection $unpackCollection,
    private RootComposer $rootComposer,
    private UnpackOptions $unpackOptions,
  ) {}

  /**
   * Get an unpacker from a given package.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to unpack.
   *
   * @return \Drupal\Composer\Plugin\RecipeUnpack\RecipeUnpacker|null
   *   The unpacker or NULL if the package cannot be unpacked.
   */
  public function create(PackageInterface $package): ?RecipeUnpacker {
    return match ($package->getType()) {
      Recipe::COMPOSER_PROJECT_TYPE => new RecipeUnpacker($package, $this->composer, $this->io, $this->rootComposer, $this->unpackCollection, $this->unpackOptions),
      default => NULL,
    };
  }

  /**
   * Determines if a package is eligible to be unpacked.
   *
   * This method checks the type of the given package to decide if it can be
   * unpacked. Only specific types of packages are eligible for unpacking.
   * For example, we do not unpack module or theme packages.
   *
   * This check is performed when a package is being installed, ensuring that
   * only the appropriate package types are handled for unpacking.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to unpack.
   *
   * @return bool
   *   TRUE if the package can be unpacked, FALSE otherwise.
   */
  public function canBeUnpacked(PackageInterface $package): bool {
    return $this->create($package) !== NULL;
  }

}
