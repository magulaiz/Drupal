<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Datetime\DrupalDateTime;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Datetime\DrupalDateTime
 * @group Datetime
 */
class DrupalDateTimeTest extends KernelTestBase {

  /**
   * Test to avoid serialization of formatTranslationCache.
   */
  public function testSleep(): void {
    $tz = new \DateTimeZone(date_default_timezone_get());
    $date = new DrupalDateTime('now', $tz, ['langcode' => 'en']);

    // Override timestamp before serialize.
    $date->setTimestamp(12345678);

    $vars = $date->__sleep();
    $this->assertContains('langcode', $vars);
    $this->assertContains('dateTimeObject', $vars);
    $this->assertNotContains('formatTranslationCache', $vars);

    $unserialized_date = unserialize(serialize($date));
    $this->assertSame(12345678, $unserialized_date->getTimestamp());
  }

}
