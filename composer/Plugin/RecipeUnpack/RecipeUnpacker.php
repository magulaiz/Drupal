<?php

namespace Drupal\Composer\Plugin\RecipeUnpack;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Drupal\Core\Recipe\Recipe;

/**
 * Unpacker for recipes.
 *
 * @internal
 */
final class RecipeUnpacker {

  public function __construct(
    private readonly PackageInterface $package,
    private readonly Composer $composer,
    private readonly IOInterface $io,
    private readonly RootComposer $rootComposer,
    private readonly UnpackCollection $unpackCollection,
    private readonly UnpackOptions $unpackOptions,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function unpackDependencies(): void {
    $this->processPackageDependencies($this->package->getRequires());
    $this->updateRootDependencies();
    $this->unpackCollection->addUnpackedPackage($this->package);
  }

  /**
   * Processes dependencies of the package that is being unpacked.
   *
   * If the dependency is the same as the current package, we skip it. If the
   * dependency a recipe, we add it into the package queue so that it will be
   * unpacked as well (this depends on the
   * UnpackerInterface::unpackRecursively() method). If the dependency is not
   * any of the above, we add it into the dependency array.
   *
   * @param array<string, \Composer\Package\Link> $package_dependency_links
   *   The package dependencies.
   */
  private function processPackageDependencies(array $package_dependency_links): void {
    foreach ($package_dependency_links as $link) {
      if ($link->getTarget() === $this->package->getName()) {
        // This dependency is the same as the current package, so let's skip it.
        continue;
      }

      if ($this->unpackOptions->ignorePackage($link->getTarget())) {
        // This dependency should not be unpacked.
        continue;
      }

      $package = $this->getPackageFromLinkTarget($link);

      if ($package && $this->unpackCollection->isUnpacked($package)) {
        // This dependency is already unpacked.
        continue;
      }

      if ($package?->getType() === Recipe::COMPOSER_PROJECT_TYPE) {
        // This dependency is of the same type as the current package being
        // unpacked. This  means that this dependency should be unpacked as
        // well, so let's add it into the package queue.
        $this->unpackCollection->enqueuePackage($package);
      }
      else {
        $this->unpackCollection->addPackageDependency($link);
      }
    }
  }

  /**
   * Updates the composer.json and composer.lock with the unpacked dependencies.
   */
  private function updateRootDependencies(): void {
    $this->updateComposerJsonPackages();
    $this->updateComposerLockContent();
  }

  /**
   * Updates the composer.json content with the package being unpacked.
   *
   * This method will add all the package dependencies to the root composer.json
   * content and also remove the package itself from the root composer.json.
   *
   * @throws \RuntimeException
   *   If the composer.json could not be updated.
   */
  private function updateComposerJsonPackages(): void {
    $composer_json = $this->rootComposer->getComposerContent();
    $composer_manipulator = $this->rootComposer->getComposerManipulator();
    $composer_config = $this->composer->getConfig();

    $link_type = isset($composer_json['require'][$this->package->getName()]) ? 'require' : 'require-dev';
    while ($package_dependency = $this->unpackCollection->popPackageDependencies()) {
      $dependency_name = $package_dependency['name'];

      if ($link_type === 'require-dev') {
        if (
          isset($composer_json['require-dev'][$dependency_name]) ||
          isset($composer_json['require'][$dependency_name])
        ) {
          // This dependency is already required.
          continue;
        }
      }
      else {
        if (isset($composer_json['require'][$dependency_name])) {
          // This dependency is already in the required section.
          continue;
        }

        if (isset($composer_json['require-dev'][$dependency_name])) {
          // This dependency is already in the require-dev section. We will
          // move it to the require section.
          $composer_manipulator->removeSubNode('require-dev', $dependency_name);
        }
      }

      // Add the dependency to the required section. If it cannot be added, then
      // throw an exception.
      if (!$composer_manipulator->addLink(
          $link_type,
          $dependency_name,
          $package_dependency['version'],
          sortPackages: $composer_config->get('sort-packages'),
      )) {
        throw new \RuntimeException(sprintf('Unable to manipulate composer.json during the unpack of %s',
          $dependency_name,
        ));
      }
    }

    if ($this->removeSelf()) {
      $composer_manipulator->removeSubNode($link_type, $this->package->getName());
    }

    $composer_manipulator->removeMainKeyIfEmpty('require-dev');
  }

  /**
   * Updates the composer.lock content.
   *
   * This method will remove the package itself from the composer.lock content
   * in the root composer.
   */
  private function updateComposerLockContent(): void {
    $composer_locker_content = $this->rootComposer->getComposerLockedContent();

    if ($this->removeSelf()) {
      // If the plugin is configured to remove the current package from composer
      // we need to also remove it from the composer.lock file.
      $max = count($composer_locker_content['packages']) - 1;

      while ($max >= 0) {
        // Find the package being unpacked in the composer.lock content and
        // remove it.
        if (isset($composer_locker_content['packages'][$max]) &&
           $composer_locker_content['packages'][$max]['name'] === $this->package->getName()
        ) {
          $this->rootComposer->removeFromComposerLock('packages', $max);
          break;
        }

        $max--;
      }
    }
  }

  /**
   * Gets the package object from a link's target.
   *
   * @param \Composer\Package\Link $dependency
   *   The link dependency.
   *
   * @return \Composer\Package\PackageInterface|null
   *   The package object.
   */
  private function getPackageFromLinkTarget(Link $dependency): ?PackageInterface {
    return $this->composer->getRepositoryManager()
      ->getLocalRepository()
      ->findPackage($dependency->getTarget(), $dependency->getConstraint());
  }

  /**
   * {@inheritdoc}
   */
  private function removeSelf(): bool {
    return $this->unpackOptions->options['remove-self'];
  }

}
