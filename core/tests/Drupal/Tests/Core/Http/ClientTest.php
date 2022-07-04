<?php

namespace Drupal\Tests\Core\Http;

use Drupal\Tests\BrowserTestBase;

/**
 * @group Http
 */
class ClientTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the effect of setting the 'sink' option.
   *
   * @see Drupal\Tests\BrowserHtmlDebugTrait\getResponseLogHandler()
   */
  public function testGuzzleSinkOption() {
    $client = \Drupal::httpClient();
    $source = "{$this->baseUrl}/core/misc/favicon.ico";
    $options = [];

    // Without the 'sink' option, the response body is a readable temp stream.
    $response = $client->get($source, $options)->getBody();
    $this->assertInstanceOf('\Psr\Http\Message\StreamInterface', $response);
    $this->assertTrue($response->isReadable());
    $this->assertEquals('php://temp', $response->getMetadata('uri'));

    // With the 'sink' option, the response body is the destination stream.
    $options['sink'] = fopen('temporary://favicon-copy.ico', 'w');
    $response = $client->get($source, $options)->getBody();
    $this->assertInstanceOf('\Psr\Http\Message\StreamInterface', $response);
    $this->assertFalse($response->isReadable());
    $this->assertEquals('temporary://favicon-copy.ico', $response->getMetadata('uri'));
  }

}
