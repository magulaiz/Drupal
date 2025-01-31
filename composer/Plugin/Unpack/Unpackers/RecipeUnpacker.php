<?php

namespace Drupal\Composer\Plugin\Unpack\Unpackers;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Drupal\Composer\Plugin\Unpack\RootComposer;
use Drupal\Composer\Plugin\Unpack\UnpackCollection;
use Drupal\Composer\Plugin\Unpack\UnpackManager;
use Drupal\Composer\Plugin\Unpack\UnpackOptions;

/**
 * Unpacker for recipes.
 *
 * @internal
 */
class RecipeUnpacker implements UnpackerInterface {

  /**
   * The ID of the unpacker which it's the same as the composer project type.
   *
   * @see \Drupal\Core\Recipe\Recipe::COMPOSER_PROJECT_TYPE
   */
  public const ID = 'drupal-recipe';

  /**
   * The unpack options for this unpacker.
   *
   * @var \Drupal\Composer\Plugin\Unpack\UnpackOptions
   */
  protected readonly UnpackOptions $unpackOptions;

  /**
   * UnpackerBase constructor.
   */
  public function __construct(
    protected readonly PackageInterface $package,
    protected readonly Composer $composer,
    protected readonly IOInterface $io,
    protected readonly RootComposer $rootComposer,
    protected readonly UnpackCollection $unpackCollection,
  ) {
    $this->unpackOptions = UnpackManager::getUnpackOptions($this->composer->getPackage(), $this);
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return self::ID;
  }

  /**
   * {@inheritdoc}
   */
  public function unpackDependencies(): void {
    $this->addPackageDependencies($this->package->getRequires());
    $this->updateRootDependencies();
    $this->unpackCollection->addUnpackedPackage($this->package);
  }

  /**
   * The dependencies of the package that is being unpacked.
   *
   * If the dependency is the same as the current package, we skip it. If the
   * dependency is of the same type as the current package being unpacked, we
   * add it into the package queue so that it will be unpacked as well (this
   * depends on the UnpackerInterface::unpackRecursively() method). If the
   * dependency is not any of the above, we add it into the dependency array.
   *
   * @param array<string, \Composer\Package\Link> $package_dependencies
   *   The package dependencies.
   */
  public function addPackageDependencies(array $package_dependencies): void {
    foreach ($package_dependencies as $package_dependency) {
      if ($package_dependency->getTarget() === $this->package->getName()) {
        // This dependency is the same as the current package, so let's skip it.
        continue;
      }

      if ($this->unpackOptions->ignorePackage($package_dependency->getTarget())) {
        // This dependency should not be unpacked.
        continue;
      }

      $dependency_package = $this->getDependencyPackage($package_dependency);

      if ($dependency_package && $this->unpackCollection->isUnpacked($dependency_package, $this->id())) {
        // This dependency is already unpacked or enqueued to be unpacked.
        continue;
      }

      if (
        $dependency_package &&
        $dependency_package->getType() === $this->package->getType()
      ) {
        // This dependency is of the same type as the current package being
        // unpacked. This  means that this dependency should be unpacked as
        // well, so let's add it into the package queue.
        $this->unpackCollection->enqueuePackage($dependency_package);
      }
      else {
        $this->unpackCollection->addPackageDependencies(
          $package_dependency->getTarget(),
          $package_dependency->getPrettyConstraint(),
        );
      }
    }
  }

  /**
   * Updates the composer.json and composer.lock with the unpacked dependencies.
   */
  public function updateRootDependencies(): void {
    $this->updateComposerJsonPackages();
    $this->updateComposerLockContent();
  }

  /**
   * Update the composer.json content with the package being unpacked.
   *
   * This method will add all the package dependencies to the root composer.json
   * content and also remove the package itself from the root composer.json.
   *
   * @throws \RuntimeException
   *   If the composer.json could not be updated.
   */
  public function updateComposerJsonPackages(): void {
    $composer_json = $this->rootComposer->getComposerContent();
    $composer_manipulator = $this->rootComposer->getComposerManipulator();

    while ($package_dependency = $this->unpackCollection->popPackageDependencies()) {
      $dependency_name = $package_dependency['name'];

      if (isset($composer_json['require'][$dependency_name])) {
        // This dependency is already in the required section.
        continue;
      }

      if (isset($composer_json['require-dev'][$dependency_name])) {
        // This dependency is already in the required-dev section.
        continue;
      }

      // Add the dependency to the required section. If it cannot be added, then
      // throw an exception.
      if (!$composer_manipulator->addLink(
          'require',
          $dependency_name,
          $package_dependency['version'],
          sortPackages: TRUE
      )) {
        throw new \RuntimeException(sprintf('Unable to manipulate composer.json during the unpack of %s',
          $dependency_name,
        ));
      }
    }

    if ($this->removeSelf()) {
      $composer_manipulator->removeSubNode('require', $this->package->getName());
    }
  }

  /**
   * Update the composer.lock content.
   *
   * This method will remove the package itself from the composer.lock content
   * in the root composer.
   */
  public function updateComposerLockContent(): void {
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
   * Get the package object from a link dependency.
   *
   * @param \Composer\Package\Link $dependency
   *   The link dependency.
   *
   * @return \Composer\Package\PackageInterface|null
   *   The package object.
   */
  protected function getDependencyPackage(Link $dependency): ?PackageInterface {
    return $this->composer->getRepositoryManager()
      ->getLocalRepository()
      ->findPackage($dependency->getTarget(), $dependency->getConstraint());
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelf(): bool {
    return $this->unpackOptions->options['remove-self'];
  }

}
