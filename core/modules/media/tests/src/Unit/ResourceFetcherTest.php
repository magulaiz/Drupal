<?php

namespace Drupal\Tests\media\Unit;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\NullBackend;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcher;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @group media
 *
 * @coversDefaultClass \Drupal\media\OEmbed\ResourceFetcher
 */
class ResourceFetcherTest extends UnitTestCase {

  /**
   * Tests deprecation messages.
   *
   * @group legacy
   */
  public function testDeprecations(): void {
    $xml = 'A fake XML resource!';
    $url = 'http://example.com/oembed?url=test.xml';

    $client = new Client();
    $providers = $this->createMock('\Drupal\media\OEmbed\ProviderRepositoryInterface');
    $cache_backend = new NullBackend('test');
    $xml_decoder = $this->prophesize(DecoderInterface::class);
    $xml_decoder->decode($xml, 'text/xml', ['url' => $url])->shouldBeCalled();

    // Create an anonymous version of the resource fetcher to expose the
    // protected parseResourceXml() method.
    $fetcher = new class ($client, $providers, $cache_backend, $xml_decoder->reveal()) extends ResourceFetcher {

      /**
       * {@inheritdoc}
       */
      public function parseResourceXml($data, $url) {
        return parent::parseResourceXml($data, $url);
      }

    };
    $this->expectDeprecation('Drupal\media\OEmbed\ResourceFetcher::parseResourceXml() is deprecated in drupal:9.3.0 and removed in drupal:10.0.0. Call ' . DecoderInterface::class . '::decode() instead. See https://www.drupal.org/project/drupal/issues/3007955');
    $fetcher->parseResourceXml($xml, $url);
  }

  /**
   * Tests how the resource fetcher handles unknown Content-Type headers.
   *
   * @covers ::fetchResource
   */
  public function testUnknownContentTypeHeader(): void {
    $headers = [
      'Content-Type' => ['text/html'],
    ];
    $body = Json::encode([
      'version' => '1.0',
      'type' => 'video',
      'html' => 'test',
    ]);
    $valid_response = new Response(200, $headers, $body);
    // Strip off the trailing '}' to produce a response that will cause a JSON
    // parse error.
    $invalid_response = new Response(200, $headers, rtrim($body, '}'));
    // A response that is valid JSON, but does not decode to an array, should
    // produce an exception as well.
    $non_array_response = new Response(200, $headers, '"Valid JSON, but not an array..."');

    $mock_handler = new MockHandler([
      $valid_response,
      $invalid_response,
      $non_array_response,
    ]);
    $client = new Client([
      'handler' => HandlerStack::create($mock_handler),
    ]);
    $providers = $this->createMock('\Drupal\media\OEmbed\ProviderRepositoryInterface');

    $fetcher = new ResourceFetcher($client, $providers, new NullBackend('default'));
    /** @var \Drupal\media\OEmbed\Resource $resource */
    $resource = $fetcher->fetchResource('valid');
    // The resource should have been successfully decoded as JSON.
    $this->assertSame('video', $resource->getType());
    $this->assertSame('test', $resource->getHtml());

    // Invalid JSON should throw an exception.
    try {
      $fetcher->fetchResource('invalid');
      $this->fail('Expected a ResourceException to be thrown for invalid JSON.');
    }
    catch (ResourceException $e) {
      $this->assertSame('Error decoding oEmbed resource: Syntax error', $e->getMessage());
    }

    // Valid JSON that does not produce an array should also throw an exception.
    $this->expectException(ResourceException::class);
    $this->expectExceptionMessage('The oEmbed resource could not be decoded.');
    $fetcher->fetchResource('non_array');
  }

}
