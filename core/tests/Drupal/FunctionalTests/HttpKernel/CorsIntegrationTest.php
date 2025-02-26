<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\HttpKernel;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests CORS provided by Drupal.
 *
 * @see sites/default/default.services.yml
 * @see \Asm89\Stack\Cors
 * @see \Asm89\Stack\CorsService
 *
 * @group Http
 */
class CorsIntegrationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'test_page_test', 'page_cache'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Set some page cache max-age so that responses are cacheable.
    $this->config('system.performance')
      ->set('cache.page.max_age', 300)
      ->save();
  }

  public function testCrossSiteRequest(): void {
    // Test default parameters.
    $cors_config = $this->container->getParameter('cors.config');
    $this->assertFalse($cors_config['enabled']);
    $this->assertSame([], $cors_config['allowedHeaders']);
    $this->assertSame([], $cors_config['allowedMethods']);
    $this->assertSame(['*'], $cors_config['allowedOrigins']);

    $this->assertFalse($cors_config['exposedHeaders']);
    $this->assertFalse($cors_config['maxAge']);
    $this->assertFalse($cors_config['supportsCredentials']);

    // Enable CORS with the default options.
    $cors_config['enabled'] = TRUE;

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Fire off a request.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://example.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'MISS');
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', '*');
    $this->assertResponseHeaderNotContains('Vary', 'Origin');

    // Fire the same exact request. This time it should be cached.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://example.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', '*');
    $this->assertResponseHeaderNotContains('Vary', 'Origin');

    // Fire a request for a different origin. Verify the CORS header.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://example.org']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('X-Drupal-Cache', 'HIT');
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', '*');
    $this->assertResponseHeaderNotContains('Vary', 'Origin');

    // Configure the CORS stack to match allowed origins using regex patterns.
    $cors_config['allowedOrigins'] = [];
    $cors_config['allowedOriginsPatterns'] = ['#^http://[a-z-]*\.valid.com$#'];

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Fire a request from an origin that isn't allowed.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://non-valid.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderDoesNotExist('Access-Control-Allow-Origin');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Specify a valid origin.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://sub-domain.valid.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://sub-domain.valid.com');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Test combining allowedOrigins and allowedOriginsPatterns.
    $cors_config['allowedOrigins'] = ['http://domainA.com'];
    $cors_config['allowedOriginsPatterns'] = ['#^http://domain[B-Z-]*\.com$#'];

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Specify an origin that does not match allowedOrigins nor
    // allowedOriginsPattern.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://non-valid.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderDoesNotExist('Access-Control-Allow-Origin');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Specify a valid origin that matches allowedOrigins.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://domainA.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://domainA.com');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Specify a valid origin that matches allowedOriginsPatterns.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://domainX.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://domainX.com');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Configure the CORS stack to allow a specific origin.
    $cors_config['allowedOrigins'] = ['http://example.com'];
    $cors_config['allowedOriginsPatterns'] = [];

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Fire a request from an origin that isn't allowed.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://non-valid.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://example.com');
    $this->assertResponseHeaderNotContains('Vary', 'Origin');

    // Specify a valid origin.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://example.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://example.com');
    $this->assertResponseHeaderNotContains('Vary', 'Origin');

    // Configure the CORS stack to allow a specific set of origins.
    $cors_config['allowedOrigins'] = ['http://example.com', 'https://drupal.org'];

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Fire a request from an origin that isn't allowed.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://non-valid.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', NULL);
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Specify a valid origin.
    $this->drupalGet('/test-page', [], ['Origin' => 'http://example.com']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'http://example.com');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Specify a valid origin.
    $this->drupalGet('/test-page', [], ['Origin' => 'https://drupal.org']);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Access-Control-Allow-Origin', 'https://drupal.org');
    $this->assertResponseHeaderContains('Vary', 'Origin');

    // Verify POST still functions with 'Origin' header set to site's domain.
    $origin = \Drupal::request()->getSchemeAndHttpHost();

    /** @var \GuzzleHttp\ClientInterface $httpClient */
    $httpClient = $this->getSession()->getDriver()->getClient()->getClient();
    $url = Url::fromUri('base:/test-page');
    /** @var \Symfony\Component\HttpFoundation\Response $response */
    $response = $httpClient->request('POST', $url->setAbsolute()->toString(), [
      'headers' => [
        'Origin' => $origin,
      ],
    ]);
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * Asserts that a response header contains a specific value.
   *
   * @param string $header
   *   The header name.
   * @param string $value
   *   The value to check for.
   */
  private function assertResponseHeaderContains(string $header, string $value): void {
    $this->assertTrue($this->responseHeaderContains($header, $value), sprintf('The response header "%s" contains "%s".', $header, $value));
  }

  /**
   * Asserts that a response header does not contain a specific value.
   *
   * @param string $header
   *   The header name.
   * @param string $value
   *   The value to check for.
   */
  private function assertResponseHeaderNotContains(string $header, string $value): void {
    $this->assertFalse($this->responseHeaderContains($header, $value), sprintf('The response header "%s" does not contain "%s".', $header, $value));
  }

  /**
   * Checks if a response header contains a specific value.
   *
   *  Multiple headers with the same name are allowed, as per RFC 2616.
   *
   * @param string $header
   *   The header name.
   * @param string $value
   *   The value to check for.
   *
   * @return bool
   *   TRUE if the response header contains the value, FALSE otherwise.
   */
  private function responseHeaderContains(string $header, string $value): bool {
    foreach ($this->getSession()->getResponseHeaders() as $response_header => $response_values) {
      foreach ($response_values as $response_value) {
        if (strtolower($response_header) === strtolower($header) && str_contains($response_value, $value)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
