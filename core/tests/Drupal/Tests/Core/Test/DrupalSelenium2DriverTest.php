<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Test;

use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver
 * @group Test
 */
class DrupalSelenium2DriverTest extends UnitTestCase {

  /**
   * Tests W3C setting is added to goog:chromeOptions as expected.
   *
   * @covers ::__construct
   */
  public function testCapabilities(): void {
    $driver = new DrupalSelenium2Driver('chrome');
    $capabilities = $driver->getDesiredCapabilities();
    $this->assertFalse($capabilities['goog:chromeOptions']['w3c']);

    $driver = new DrupalSelenium2Driver('chrome', ['goog:chromeOptions' => ['args' => ['--headless']]]);
    $capabilities = $driver->getDesiredCapabilities();
    $this->assertFalse($capabilities['goog:chromeOptions']['w3c']);
    $this->assertSame(['--headless'], $capabilities['goog:chromeOptions']['args']);

    $driver = new DrupalSelenium2Driver('chrome', ['goog:chromeOptions' => ['w3c' => TRUE]]);
    $capabilities = $driver->getDesiredCapabilities();
    $this->assertTrue($capabilities['goog:chromeOptions']['w3c']);
  }

  /**
   * Tests "chromeOptions" deprecation.
   *
   * @group legacy
   *
   * @covers ::__construct
   */
  public function testChromeOptions(): void {
    $this->expectDeprecation('The "chromeOptions" array key is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use "goog:chromeOptions instead. See https://www.drupal.org/node/3422624');
    $driver = new DrupalSelenium2Driver('chrome', ['chromeOptions' => ['args' => ['--headless']]]);
    $capabilities = $driver->getDesiredCapabilities();
    $this->assertFalse($capabilities['goog:chromeOptions']['w3c']);
    $this->assertSame(['--headless'], $capabilities['goog:chromeOptions']['args']);
  }

}
