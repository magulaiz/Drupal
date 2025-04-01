<?php

namespace Drupal\Composer\Plugin\RecipeUnpack;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Drupal\Core\Recipe\Recipe;

/**
 * Core class to handle operations on dependencies.
 */
final class UnpackManager {

  /**
   * The unpack collection.
   *
   * @var \Drupal\Composer\Plugin\RecipeUnpack\UnpackCollection
   */
  private UnpackCollection $unpackCollection;

  /**
   * The root composer with the root dependencies to be manipulated.
   *
   * @var \Drupal\Composer\Plugin\RecipeUnpack\RootComposer
   */
  private RootComposer $rootComposer;

  /**
   * The unpack options.
   *
   * @var \Drupal\Composer\Plugin\RecipeUnpack\unpackOptions
   */
  public readonly UnpackOptions $unpackOptions;

  public function __construct(
    private readonly Composer $composer,
    private readonly IOInterface $io,
  ) {
    $this->unpackCollection = new UnpackCollection();
    $this->rootComposer = new RootComposer($composer, $this->io);
    $this->unpackOptions = UnpackOptions::create($composer->getPackage()->getExtra());
  }

  /**
   * Register a package for unpacking.
   *
   * If the package can be unpacked, it will be added into the package queue in
   * the UnpackCollection.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to register for unpacking.
   */
  public function registerPackage(PackageInterface $package): void {
    if ($package->getType() === Recipe::COMPOSER_PROJECT_TYPE) {
      $this->unpackCollection->enqueuePackage($package);
    }
  }

  /**
   * Unpack the packages in the queue.
   */
  public function unpack(): void {
    while ($package = $this->unpackCollection->popPackageQueue()) {
      $unpacker = new RecipeUnpacker(
        $package,
        $this->composer,
        $this->io,
        $this->rootComposer,
        $this->unpackCollection,
        $this->unpackOptions,
      );
      $unpacker->unpackDependencies();
    }

    $this->rootComposer->updateComposer();

    foreach ($this->unpackCollection->getUnpackedPackages() as $package) {
      /** @var \Composer\Package\PackageInterface $package */
      $this->io->write("The <info>{$package->getName()}</info> recipe was unpacked successfully.");
    }
  }

}
