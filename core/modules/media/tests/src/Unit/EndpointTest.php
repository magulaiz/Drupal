<?php

declare(strict_types=1);

namespace Drupal\Tests\media\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\media\OEmbed\Endpoint;
use Drupal\Tests\UnitTestCase;

/**
 * @group media
 */
#[CoversClass(\Drupal\media\OEmbed\Endpoint::class)]
class EndpointTest extends UnitTestCase {

  public function testMatchUrl(): void {
    $endpoint = new Endpoint(
      'https://www.youtube.com/oembed',
      $this->createMock('\Drupal\media\OEmbed\Provider'),
      ['https://*.youtube.com/playlist?list=*']
    );
    $this->assertTrue($endpoint->matchUrl('https://www.youtube.com/playlist?list=aBc-EzAs123'));
  }

  public function testCaseSensitiveMatch(): void {
    $endpoint = new Endpoint(
      'https://www.example.com/oembed',
      $this->createMock('\Drupal\media\OEmbed\Provider'),
      ['https://*.example.com/Video/*'],
    );
    $this->assertTrue($endpoint->matchUrl('https://foo.example.com/Video/bar'));
  }

}
