<?php

namespace Drupal\Composer\Plugin\Scaffold;

use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Produces code for the DrupalInstalled file.
 *
 * @internal
 */
class DrupalInstalledTemplate {

  /**
   * Gets the code for the DrupalInstaller class.
   *
   * @return string
   *   The PHP code to write to the Drupal locations class.
   */
  public static function getCode(PackageInterface $root_package, InstalledRepositoryInterface $repository): string {
    // Write out a hash of the version information to a file so we can use it.
    $versions = array_reduce($repository->getPackages(), fn (string $carry, PackageInterface $package) => $carry . $package->getUniqueName() . '-' . $package->getSourceReference() . '|', '');
    // Add the root_package package version info so custom code changes and root_package
    // package version changes result in the hash changing.
    $versions .= $root_package->getUniqueName() . '-' . $root_package->getSourceReference();
    $version_hash = hash('xxh3', $versions);
    return <<<EOF
      <?php

      namespace Drupal;

      /**
       * A class containing information determined during composer installation.
       *
       * @see \Drupal\Composer\Plugin\Scaffold\Plugin::preAutoloadDump())
       */
      class DrupalInstalled {

        /**
         * A hash of all the installed packages and their versions.
         */
        public const string VERSIONS_HASH = '$version_hash';

      }

      EOF;
  }

}
