<?php

namespace Drupal\Composer\Plugin\Unpack;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\IO\IOInterface;
use Composer\Installer\PackageEvent;
use Composer\Package\PackageInterface;
use Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerFactory;
use Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerInterface;

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

  public function __construct(
    Composer $composer,
    private readonly IOInterface $io,
  ) {
    $this->unpackCollection = new UnpackCollection();
    $this->rootComposer = new RootComposer($composer, $this->io);
    $this->unpackerFactory = new UnpackerFactory(
      $composer,
      $this->io,
      $this->unpackCollection,
      $this->rootComposer,
    );
  }

  /**
   * Register a package for unpacking.
   *
   * If the package is unpackable, it will be added into the package queue in
   * the UnpackCollection.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Composer package event sent on install/update/remove.
   */
  public function registerPackage(PackageEvent $event): void {
    $package = self::getPackage($event);
    if ($this->unpackerFactory->isUnpackable($package)) {
      $this->unpackCollection->enqueuePackage($package);
    }
  }

  /**
   * Unpack the packages in the queue.
   */
  public function unpack(): void {
    while ($package = $this->unpackCollection->popPackageQueue()) {
      $unpacker = $this->unpackerFactory->create($package);
      if ($unpacker) {
        $unpacker->unpackDependencies();
      }
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
   * @param \Drupal\Composer\Plugin\Unpack\UnpackerInterface $unpacker
   *   The unpacker to use.
   *
   * @return \Drupal\Composer\Plugin\Unpack\Unpackers\UnpackOptions
   *   The unpack options.
   */
  public static function getUnpackOptions(PackageInterface $package, UnpackerInterface $unpacker): UnpackOptions {
    return UnpackOptions::create($package->getExtra());
  }

  /**
   * Get the package from a package event.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Composer package event sent on install/update/remove.
   *
   * @return \Composer\Package\PackageInterface
   *   The package from the event.
   */
  private static function getPackage(PackageEvent $event): PackageInterface {
    $operation = $event->getOperation();
    return match (get_class($operation)) {
      InstallOperation::class => $operation->getPackage(),
      UpdateOperation::class => $operation->getTargetPackage(),
    };
  }

}
