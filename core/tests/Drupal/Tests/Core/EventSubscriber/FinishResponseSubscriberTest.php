<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\EventSubscriber\FinishResponseSubscriber;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @coversDefaultClass \Drupal\Core\EventSubscriber\FinishResponseSubscriber
 * @group EventSubscriber
 */
class FinishResponseSubscriberTest extends UnitTestCase {

  /**
   * The mock Kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $kernel;

  /**
   * The mock language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The mock request policy.
   *
   * @var \Drupal\Core\PageCache\RequestPolicyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $requestPolicy;

  /**
   * The mock response policy.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $responsePolicy;

  /**
   * The mock cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheContextsManager;

  /**
   * The mock time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $time;

  protected function setUp(): void {
    parent::setUp();

    $this->kernel = $this->createMock(HttpKernelInterface::class);
    $this->languageManager = $this->createMock(LanguageManagerInterface::class);
    $this->requestPolicy = $this->createMock(RequestPolicyInterface::class);
    $this->responsePolicy = $this->createMock(ResponsePolicyInterface::class);
    $this->cacheContextsManager = $this->createMock(CacheContextsManager::class);
    $this->time = $this->createMock(TimeInterface::class);
  }

  /**
   * Finish subscriber should set some default header values.
   *
   * @covers ::onRespond
   */
  public function testDefaultHeaders(): void {
    $finishSubscriber = new FinishResponseSubscriber(
      $this->languageManager,
      $this->getConfigFactoryStub(),
      $this->requestPolicy,
      $this->responsePolicy,
      $this->cacheContextsManager,
      $this->time,
      FALSE
    );

    $this->languageManager->method('getCurrentLanguage')
      ->willReturn(new Language(['id' => 'en']));

    $request = $this->createMock(Request::class);
    $response = $this->createMock(Response::class);
    $response->headers = new ResponseHeaderBag();
    $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $finishSubscriber->onRespond($event);

    $this->assertEquals(['en'], $response->headers->all('Content-language'));
    $this->assertEquals(['nosniff'], $response->headers->all('X-Content-Type-Options'));
    $this->assertEquals(['SAMEORIGIN'], $response->headers->all('X-Frame-Options'));
  }

  /**
   * Finish subscriber should set Cache-Control and Expires header values.
   *
   * @dataProvider providerTestCacheabilityHeaders
   * @covers ::onRespond
   */
  public function testCacheabilityHeaders(int $request_time, ?int $max_age, bool $request_policy_cacheable, bool $response_policy_cacheable, array $expected_headers): void {
    $finishSubscriber = new FinishResponseSubscriber(
      $this->languageManager,
      $this->getConfigFactoryStub([
        'system.performance' => [
          'cache' => [
            'page' => [
              'max_age' => $max_age,
            ],
          ],
        ],
      ]),
      $this->requestPolicy,
      $this->responsePolicy,
      $this->cacheContextsManager,
      $this->time,
      FALSE
    );

    $this->languageManager->method('getCurrentLanguage')
      ->willReturn(new Language(['id' => 'en']));

    // Configure request and response policies to allow/deny caching.
    $this->requestPolicy->method('check')
      ->willReturn($request_policy_cacheable ? RequestPolicyInterface::ALLOW : RequestPolicyInterface::DENY);
    $this->responsePolicy->method('check')
      ->willReturn($response_policy_cacheable ? NULL : ResponsePolicyInterface::DENY);

    // Mock the request, the response and the event for onRespond().
    $request = $this->createMock(Request::class);
    $request->headers = new HeaderBag();
    $this->time->method('getRequestTime')
      ->willReturn(0);
    $response = $this->getMockBuilder(CacheableResponse::class)->onlyMethods([])->getMock();
    $response->headers = new ResponseHeaderBag();
    $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $finishSubscriber->onRespond($event);

    // Assert headers.
    foreach ($expected_headers as $header_name => $header_value) {
      $this->assertEquals([$header_value], $response->headers->all($header_name));
    }
  }

  /**
   * Data provider for testCacheabilityHeaders().
   */
  public static function providerTestCacheabilityHeaders(): array {
    // A request time of 0 is used to simplify the test,
    // which equals to the Unix epoch.
    $request_time = 0;
    return [
      'Cacheable' => [
        $request_time,
        3600,
        TRUE,
        TRUE,
        [
          'Cache-Control' => 'max-age=3600, public',
          'Expires' => gmdate('D, d M Y H:i:s', $request_time + 3600) . ' GMT',
        ],
      ],
      'Not cacheable by cache.page.max_age config' => [
        $request_time,
        0,
        TRUE,
        TRUE,
        [
          'Cache-Control' => 'must-revalidate, no-cache, private',
          'Expires' => 'Sun, 19 Nov 1978 05:00:00 GMT',
        ],
      ],
      'Not cacheable by request policy' => [
        $request_time,
        0,
        FALSE,
        TRUE,
        [
          'Cache-Control' => 'must-revalidate, no-cache, private',
          'Expires' => 'Sun, 19 Nov 1978 05:00:00 GMT',
        ],
      ],
      'Not cacheable by response policy' => [
        $request_time,
        0,
        TRUE,
        FALSE,
        [
          'Cache-Control' => 'must-revalidate, no-cache, private',
          'Expires' => 'Sun, 19 Nov 1978 05:00:00 GMT',
        ],
      ],
    ];
  }

  /**
   * Finish subscriber should not overwrite existing header values.
   *
   * @covers ::onRespond
   */
  public function testExistingHeaders(): void {
    $finishSubscriber = new FinishResponseSubscriber(
      $this->languageManager,
      $this->getConfigFactoryStub(),
      $this->requestPolicy,
      $this->responsePolicy,
      $this->cacheContextsManager,
      $this->time,
      FALSE
    );

    $this->languageManager->method('getCurrentLanguage')
      ->willReturn(new Language(['id' => 'en']));

    $request = $this->createMock(Request::class);
    $response = $this->createMock(Response::class);
    $response->headers = new ResponseHeaderBag();
    $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $response->headers->set('X-Content-Type-Options', 'foo');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('Expires', 'Mon, 10 Feb 2025 00:00:00 GMT');

    $finishSubscriber->onRespond($event);

    $this->assertEquals(['en'], $response->headers->all('Content-language'));
    // 'X-Content-Type-Options' will be unconditionally set by core.
    $this->assertEquals(['nosniff'], $response->headers->all('X-Content-Type-Options'));
    $this->assertEquals(['DENY'], $response->headers->all('X-Frame-Options'));
    $this->assertEquals(['Mon, 10 Feb 2025 00:00:00 GMT'], $response->headers->all('Expires'));
  }

}
