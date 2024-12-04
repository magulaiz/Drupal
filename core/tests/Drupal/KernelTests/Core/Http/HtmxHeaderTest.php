<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Http;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the header builder.
 *
 * @group Http
 *
 * @coversDefaultClass \Drupal\Core\Http\HtmxResponseHeaders
 */
final class HtmxHeaderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user', 'htmx_headers_test'];

  /**
   * Injected entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Set up the test.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->installEntitySchema('user_role');
    $this->installEntitySchema('user');
    $this->installConfig(['user']);
    $this->container
      ->get('module_handler')
      ->loadInclude('user', 'install');
    user_install();
    /** @var \Drupal\user\Entity\Role $anonymous */
    $anonymous = $this->entityTypeManager->getStorage('user_role')->load('anonymous');
    $anonymous->grantPermission('access content')->save();
  }

  /**
   * Test all the headers.
   *
   * @covers ::location
   * @covers ::pushUrl
   * @covers ::replaceUrl
   * @covers ::refresh
   * @covers ::reswap
   * @dataProvider hxHeaderDataProvider
   */
  public function testHeaders(string $path, string $headerName, string $headerValue): void {
    $httpKernel = $this->container->get('http_kernel');
    $request = Request::create($path);
    $response = $httpKernel->handle($request);
    $this->assertTrue($response->headers->has($headerName));
    $value = $response->headers->get($headerName);
    $this->assertEquals($headerValue, $value);
  }

  /**
   * Data provider for ::testHeaders.
   */
  public static function hxHeaderDataProvider(): array {
    return [
      ['/test-htmx-headers/location-url', 'HX-Location', '/'],
      [
        '/test-htmx-headers/location-json',
        'HX-Location',
        '{"path":"\/","source":"source-value","event":"event-value","headers":{"Header-one":"one"},"handler":"handler-value","target":"target-value","swap":"swap-value","select":"select-value","values":{"one":"1","two":"2"}}',
      ],
      ['/test-htmx-headers/push-url', 'HX-Push-Url', '/'],
      ['/test-htmx-headers/push-url-false', 'HX-Push-Url', 'false'],
      ['/test-htmx-headers/replace-url', 'HX-Replace-Url', '/'],
      ['/test-htmx-headers/replace-url-false', 'HX-Replace-Url', 'false'],
      ['/test-htmx-headers/redirect', 'HX-Redirect', '/'],
      ['/test-htmx-headers/refresh', 'HX-Refresh', 'true'],
      ['/test-htmx-headers/reswap', 'HX-Reswap', 'swap-value'],
      ['/test-htmx-headers/retarget', 'HX-Retarget', 'retarget-value'],
      ['/test-htmx-headers/reselect', 'HX-Reselect', 'reselect-value'],
    ];
  }

  /**
   * Test trigger headers with just an event name.
   */
  public function testSimpleTriggers(): void {
    $httpKernel = $this->container->get('http_kernel');
    $request = Request::create('/test-htmx-headers/triggers');
    $response = $httpKernel->handle($request);
    $this->assertTrue($response->headers->has('HX-Trigger'));
    $this->assertTrue($response->headers->has('HX-Trigger-After-Settle'));
    $this->assertTrue($response->headers->has('HX-Trigger-After-Swap'));
    $value = $response->headers->get('HX-Trigger');
    $this->assertEquals('trigger-value', $value);
    $value = $response->headers->get('HX-Trigger-After-Settle');
    $this->assertEquals('trigger-value', $value);
    $value = $response->headers->get('HX-Trigger-After-Swap');
    $this->assertEquals('trigger-value', $value);
  }

  /**
   * Test triggers with JSON payload.
   */
  public function testJsonTriggers(): void {
    $httpKernel = $this->container->get('http_kernel');
    $request = Request::create('/test-htmx-headers/triggers-json');
    $response = $httpKernel->handle($request);
    $this->assertTrue($response->headers->has('HX-Trigger'));
    $this->assertTrue($response->headers->has('HX-Trigger-After-Settle'));
    $this->assertTrue($response->headers->has('HX-Trigger-After-Swap'));
    $value = $response->headers->get('HX-Trigger');
    $this->assertEquals('{"showMessage":{"level":"info","message":"Here Is A Message"}}', $value);
    $value = $response->headers->get('HX-Trigger-After-Settle');
    $this->assertEquals('{"showMessage":{"level":"info","message":"Here Is A Message"}}', $value);
    $value = $response->headers->get('HX-Trigger-After-Swap');
    $this->assertEquals('{"showMessage":{"level":"info","message":"Here Is A Message"}}', $value);
  }

}
