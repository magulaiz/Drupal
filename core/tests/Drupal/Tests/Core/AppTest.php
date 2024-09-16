<?php

declare(strict_types=1);

namespace Drupal\Tests\Core;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the App class.
 *
 * @coversDefaultClass \Drupal\Core\App
 * @group AppTest
 */
class AppTest extends TestCase {

  /**
   * @covers ::getBaseUrl
   * @covers ::prepareBaseUrl
   *
   * @dataProvider providerTestGetBaseUrl
   */
  public function testGetBaseUrl($root, $script, $url, $expected_base_url): void {
    $request = Request::create($url, 'GET', [], [], [], [
      'SCRIPT_FILENAME' => $script,
      'SCRIPT_NAME' => basename($script),
    ]);
    $app = $this->getMockBuilder('Drupal\Core\App')
      ->setConstructorArgs([$root])
      ->onlyMethods(['realpath'])
      ->getMock();
    $app
      ->method('realpath')
      ->willReturnCallback(function ($path) {
        return $path;
      });
    $app->setRequest($request);
    $this->assertEquals($expected_base_url, $app->getBaseUrl());
  }

  /**
   * Provides test data for testGetBaseUrl().
   */
  public static function providerTestGetBaseUrl(): array {
    return [
      [
        '/var/www',
        '/var/www/index.php',
        'http://example.com/index.php',
        'http://example.com',
      ],
      [
        '/var/www',
        '/var/www/index.php',
        'http://example.com/index.php/foo',
        'http://example.com',
      ],
      [
        '/var/www',
        '/var/www/core/install.php',
        'http://example.com/core/install.php',
        'http://example.com',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/index.php',
        'http://example.com/drupal/index.php',
        'http://example.com/drupal',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/core/install.php',
        'http://example.com/drupal/core/install.php',
        'http://example.com/drupal',
      ],

      [
        '/var/www',
        '/var/www/index.php',
        'https://example.com/index.php',
        'https://example.com',
      ],
      [
        '/var/www',
        '/var/www/core/install.php',
        'https://example.com/core/install.php',
        'https://example.com',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/index.php',
        'https://example.com/drupal/index.php',
        'https://example.com/drupal',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/core/install.php',
        'https://example.com/drupal/core/install.php',
        'https://example.com/drupal',
      ],

      [
        '/var/www',
        '/var/www/index.php',
        'http://example.com:8080/index.php',
        'http://example.com:8080',
      ],
      [
        '/var/www',
        '/var/www/core/install.php',
        'http://example.com:8080/core/install.php',
        'http://example.com:8080',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/index.php',
        'http://example.com:8080/drupal/index.php',
        'http://example.com:8080/drupal',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/core/install.php',
        'http://example.com:8080/drupal/core/install.php',
        'http://example.com:8080/drupal',
      ],
    ];
  }

  /**
   * @covers ::getBasePath
   * @covers ::prepareBasePath
   *
   * @dataProvider providerTestGetBasePath
   */
  public function testGetBasePath($root, $script, $url, $expected_base_url): void {
    $request = Request::create($url, 'GET', [], [], [], [
      'SCRIPT_FILENAME' => $script,
      'SCRIPT_NAME' => basename($script),
    ]);
    $app = $this->getMockBuilder('Drupal\Core\App')
      ->setConstructorArgs([$root])
      ->onlyMethods(['realpath'])
      ->getMock();
    $app
      ->method('realpath')
      ->willReturnCallback(function ($path) {
        return $path;
      });
    $app->setRequest($request);
    $this->assertEquals($expected_base_url, $app->getBasePath());
  }

  /**
   * Provides test data for testGetBasePath().
   */
  public static function providerTestGetBasePath(): array {
    return [
      [
        '/var/www',
        '/var/www/index.php',
        'http://example.com/index.php',
        '',
      ],
      [
        '/var/www',
        '/var/www/index.php',
        'http://example.com/index.php/foo',
        '',
      ],
      [
        '/var/www',
        '/var/www/core/install.php',
        'http://example.com/core/install.php',
        '',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/index.php',
        'http://example.com/drupal/index.php',
        '/drupal',
      ],
      [
        '/var/www/drupal',
        '/var/www/drupal/core/install.php',
        'http://example.com/drupal/core/install.php',
        '/drupal',
      ],
    ];
  }

}
