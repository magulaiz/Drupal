<?php

declare(strict_types=1);

namespace core\tests\Drupal\Tests\Composer\Plugin\Unpack;

use core\tests\Drupal\Tests\Composer\Plugin\FixturesBase;

class Fixtures extends FixturesBase {

  /**
   * {@inheritdoc}
   */
  public function projectRoot(): string {
    return realpath(__DIR__) . '/../../../../../../../composer/Plugin/Unpack';
  }

  /**
   * {@inheritdoc}
   */
  public function allFixturesDir(): string {
    return realpath(__DIR__ . '/fixtures');
  }

  /**
   * {@inheritdoc}
   */
  public function tmpDir(string $prefix): string {
    $prefix .= static::persistentPrefix();
    $tmpDir = sys_get_temp_dir() . '/unpack-' . $prefix . uniqid(md5($prefix . microtime()), TRUE);
    $this->tmpDirs[] = $tmpDir;
    return $tmpDir;
  }

}
