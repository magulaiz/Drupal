<?php

namespace Drupal\Tests\Component\FileSystem;

use Drupal\Component\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Tests deprecated file system methods.
 *
 * @coversDefaultClass \Drupal\Component\FileSystem\FileSystem
 * @group FileSystem
 * @group legacy
 */
class LegacyFileSystemTest extends TestCase {

  use ExpectDeprecationTrait;

  /**
   * Tests deprecated getOsTemporaryDirectory()
   */
  public function testDeprecatedGetOsTemporaryDirectory() {
    $this->expectDeprecation('Drupal\Component\FileSystem\FileSystem::getOsTemporaryDirectory is deprecated in drupal:10.0.0 and is removed from drupal:11.0.0. Use sys_get_temp_dir() instead. See https://www.drupal.org/node/3225275');
    $this->assertEquals(sys_get_temp_dir(), FileSystem::getOsTemporaryDirectory());
  }

}
