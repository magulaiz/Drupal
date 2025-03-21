<?php

namespace Drupal\Composer\Plugin\Unpack;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerFactory;

/**
 * Core class to handle operations on dependencies.
 */
final class UnpackManager {

  /**
   * The unpack collection.
   *
   * @var \Drupal\Composer\Plugin\Unpack\UnpackCollection
   */
  private UnpackCollection $unpackCollection;

  /**
   * The unpacker factory.
   *
   * @var \Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerFactory
   */
  private UnpackerFactory $unpackerFactory;

  /**
   * The root composer with the root dependencies to be manipulated.
   *
   * @var \Drupal\Composer\Plugin\Unpack\RootComposer
   */
  private RootComposer $rootComposer;

  /**
   * The unpack options.
   *
   * @var \Drupal\Composer\Plugin\Unpack\unpackOptions
   */
  public readonly UnpackOptions $unpackOptions;

  public function __construct(
    Composer $composer,
    private readonly IOInterface $io,
  ) {
    $this->unpackCollection = new UnpackCollection();
    $this->rootComposer = new RootComposer($composer, $this->io);
    $this->unpackOptions = UnpackManager::getUnpackOptions($composer->getPackage());
    $this->unpackerFactory = new UnpackerFactory(
      $composer,
      $this->io,
      $this->unpackCollection,
      $this->rootComposer,
      $this->unpackOptions
    );
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
    if ($this->unpackerFactory->canBeUnpacked($package)) {
      $this->unpackCollection->enqueuePackage($package);
    }
  }

  /**
   * Unpack the packages in the queue.
   */
  public function unpack(): void {
    while ($package = $this->unpackCollection->popPackageQueue()) {
      $unpacker = $this->unpackerFactory->create($package);
      $unpacker?->unpackDependencies();
    }

    $this->rootComposer->updateComposer();

    foreach ($this->unpackCollection->getUnpackedPackages() as $package) {
      /** @var \Composer\Package\PackageInterface $package */
      $this->io->write("Package <info>{$package->getName()}</info> of type <info>{$package->getType()}</info> was unpacked successfully.");
    }
  }

  /**
   * Get the unpack options for a package.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to unpack.
   *
   * @return \Drupal\Composer\Plugin\Unpack\UnpackOptions
   *   The unpack options.
   */
  public static function getUnpackOptions(PackageInterface $package): UnpackOptions {
    return UnpackOptions::create($package->getExtra());
  }

}
