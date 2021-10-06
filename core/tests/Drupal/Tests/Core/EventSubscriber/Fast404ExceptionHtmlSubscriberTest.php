<?php

namespace Drupal\Tests\Core\EventSubscriber;

use Drupal\Core\EventSubscriber\Fast404ExceptionHtmlSubscriber;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @coversDefaultClass \Drupal\Core\EventSubscriber\Fast404ExceptionHtmlSubscriber
 * @group EventSubscriber
 */
class Fast404ExceptionHtmlSubscriberTest extends UnitTestCase {

  /**
   * The mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * @covers ::on4xx
   * @dataProvider providerTestOn404
   */
  public function testOn404($enabled, $uri, $expected_content) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject $configFactory **/
    $configFactory = $this->getConfigFactoryStub([
      'system.performance' => [
        'fast_404' => [
          'enabled' => $enabled,
          'exclude_paths' => '/\/(?:styles)\//',
          'paths' => '/\.(?:png)$/i',
          'html' => 'Fast 404',
        ],
      ],
    ]);
    $kernel = $this->prophesize(HttpKernelInterface::class);
    $request = Request::create($uri);
    $themed_response = new Response('Themed 404', Response::HTTP_NOT_FOUND);
    $event = new ExceptionEvent($kernel->reveal(), $request, HttpKernelInterface::MASTER_REQUEST, new NotFoundHttpException('foo'));
    $event->setResponse($themed_response);

    $subscriber = new Fast404ExceptionHtmlSubscriber($configFactory, $kernel->reveal());
    $subscriber->on404($event);
    $response = $event->getResponse();

    $this->assertInstanceOf(Response::class, $response);
    $this->assertEquals($expected_content, $response->getContent());
    $this->assertEquals(404, $response->getStatusCode());
  }

  /**
   * Data provider for the testOn404 function.
   *
   * @return array[]
   *   Data.
   */
  public function providerTestOn404() {
    return [
      'fast 404 disabled' => [
        FALSE,
        '/test.png',
        'Themed 404',
      ],
      'fast 404 enabled / the excluded path' => [
        TRUE,
        '/styles/test-style',
        'Themed 404',
      ],
      'fast 404 enabled / the path that should return a simple 404 page' => [
        TRUE,
        '/test.png',
        'Fast 404',
      ],
      'fast 404 enabled / the path that should return a full themed 404 page' => [
        TRUE,
        '/test',
        'Themed 404',
      ],
    ];
  }

}
