<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel\Plugin\migrate\source\d6;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\migrate\Kernel\MigrateSqlSourceTestBase;

/**
 * Tests the d6_user_picture_file source plugin.
 *
 * @group user
 */
#[CoversClass(\Drupal\user\Plugin\migrate\source\d6\UserPictureFile::class)]
class UserPictureFileTest extends MigrateSqlSourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'migrate_drupal'];

  /**
   * {@inheritdoc}
   */
  public static function providerSource() {
    $tests = [];

    // The source data.
    $tests[0]['source_data']['users'] = [
      [
        'uid' => '2',
        'picture' => 'core/tests/fixtures/files/image-test.jpg',
      ],
      [
        'uid' => '15',
        'picture' => '',
      ],
    ];

    // The expected results.
    $tests[0]['expected_data'] = [
      [
        'uid' => '2',
        'picture' => 'core/tests/fixtures/files/image-test.jpg',
      ],
    ];

    return $tests;
  }

}
