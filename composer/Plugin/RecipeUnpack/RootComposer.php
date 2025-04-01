<?php

namespace Drupal\Composer\Plugin\RecipeUnpack;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Composer\Package\Locker;

/**
 * Provides access to the root composer.json contents.
 *
 * This class should be used as a singleton so that multiple unpackers can
 * access the same root composer.json content.
 */
final class RootComposer {

  /**
   * The root composer.json content.
   *
   * @var array<string, mixed>
   */
  private ?array $composerContent = NULL;

  /**
   * The JSON manipulator for the contents of the root composer.json.
   *
   * @var \Composer\Json\JsonManipulator
   */
  private JsonManipulator $composerManipulator;

  /**
   * The locked root composer.json content.
   *
   * @var array<string, mixed>|null
   */
  private ?array $composerLockedContent = NULL;

  public function __construct(
    private readonly Composer $composer,
    private readonly IOInterface $io,
  ) {}

  /**
   * Gets the root composer.json content.
   *
   * @return array<string, mixed>
   *   The root composer.json content.
   */
  public function getComposerContent(): array {
    $this->composerContent ??= json_decode(self::getRawComposerContent(), TRUE);
    return $this->composerContent;
  }

  /**
   * Retrieves the JSON manipulator for the contents of the root composer.json.
   *
   * @return \Composer\Json\JsonManipulator
   *   The JSON manipulator.
   */
  public function getComposerManipulator(): JsonManipulator {
    $this->composerManipulator ??= new JsonManipulator(self::getRawComposerContent());
    return $this->composerManipulator;
  }

  /**
   * Gets the locked root composer.json content.
   *
   * @return array<string, mixed>
   *   The locked root composer.json content.
   */
  public function getComposerLockedContent(): array {
    $this->composerLockedContent ??= $this->composer->getLocker()->getLockData();
    return $this->composerLockedContent;
  }

  /**
   * Removes an element from the composer lock.
   *
   * @param string $key
   *   The key of the element to remove.
   * @param string $index
   *   The index of the element to remove.
   */
  public function removeFromComposerLock(string $key, string $index): void {
    unset($this->composerLockedContent[$key][$index]);
  }

  /**
   * Updates the root composer.json and composer.lock files.
   *
   * @throws \RuntimeException
   *   If the root composer could not be updated.
   */
  public function updateComposer(): void {
    $this->updateComposerJsonFile();
    $this->updateComposerLockFile();
  }

  /**
   * Updates the root composer.json file with the unpacked dependencies.
   *
   * @throws \RuntimeException
   *   If the root composer could not be updated.
   */
  private function updateComposerJsonFile(): void {
    $composer_json = Factory::getComposerFile();
    if (!file_put_contents($composer_json, $this->getComposerManipulator()->getContents())) {
      throw new \RuntimeException(sprintf('Could not update %s', $composer_json));
    }
  }

  /**
   * Updates the root composer.lock file.
   */
  private function updateComposerLockFile(): void {
    $composer_content = self::getRawComposerContent();
    $composer_locker_content = $this->getComposerLockedContent();
    $composer_locker_content['packages'] = array_values($composer_locker_content['packages']);
    $composer_locker_content['packages-dev'] = array_values($composer_locker_content['packages-dev']);
    $composer_locker_content['content-hash'] = Locker::getContentHash($composer_content);
    $lock_file_path = substr(Factory::getComposerFile(), 0, -4) . 'lock';
    $lock_file = new JsonFile($lock_file_path, io: $this->io);
    $lock_file->write($composer_locker_content);
    // Forcefully remove files under vendor.
    $locker = new Locker($this->io, $lock_file, $this->composer->getInstallationManager(), $composer_content);
    $this->composer->setLocker($locker);
  }

  /**
   * Gets the raw contents of the root composer.json file.
   *
   * @return string
   *   The raw contents of the root composer.json file.
   */
  private static function getRawComposerContent(): string {
    return file_get_contents(Factory::getComposerFile());
  }

}
