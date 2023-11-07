<?php

namespace Drupal\Tests\media\Unit;

use Drupal\Core\PrivateKey;
use Drupal\Core\Routing\RequestContext;
use Drupal\media\IFrameUrlHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\media\IFrameUrlHelper
 *
 * @group media
 */
class IFrameUrlHelperTest extends UnitTestCase {

  /**
   * Data provider for testIsSecure().
   *
   * @see ::testIsSecure()
   *
   * @return array
   */
  public function providerIsSecure() {
    return [
      'no domain' => [
        '/path/to/media.php',
        'https://www.example.com/',
        FALSE,
      ],
      'no base URL domain' => [
        'https://www.example.com/media.php',
        '/invalid/base/url',
        FALSE,
      ],
      'same domain' => [
        'https://www.example.com/media.php',
        'https://www.example.com/',
        FALSE,
      ],
      'different domain' => [
        'https://www.example.com/media.php',
        'https://www.example-assets.com/',
        TRUE,
      ],
      'same subdomain' => [
        'http://foo.example.com/media.php',
        'http://foo.example.com/',
        FALSE,
      ],
      'different subdomain' => [
        'http://assets.example.com/media.php',
        'http://foo.example.com/',
        TRUE,
      ],
      'subdomain and top-level domain' => [
        'http://assets.example.com/media.php',
        'https://example.com/',
        TRUE,
      ],
    ];
  }

  /**
   * Tests that isSecure() behaves properly.
   *
   * @param string $url
   *   The URL to test for security.
   * @param string $base_url
   *   The base URL to compare $url against.
   * @param bool $secure
   *   The expected result of isSecure().
   *
   * @covers ::isSecure
   *
   * @dataProvider providerIsSecure
   */
  public function testIsSecure($url, $base_url, $secure) {
    $request_context = $this->createMock(RequestContext::class);
    $request_context->expects($this->any())
      ->method('getCompleteBaseUrl')
      ->willReturn($base_url);

    $url_helper = new IFrameUrlHelper(
      $request_context,
      $this->createMock(PrivateKey::class)
    );

    $this->assertSame($secure, $url_helper->isSecure($url));
  }

}
