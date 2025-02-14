<?php

namespace Drupal\Tests\Core\Render\Hypermedia;

use Drupal\Core\Render\Element\RenderElementBase;
use Drupal\Core\Render\Hypermedia\Htmx;
use Drupal\Core\Render\Hypermedia\Operations\Insert;
use Drupal\Core\Render\Hypermedia\Operations\Replace;
use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for implementations of HtmxOperationInterface.
 *
 * @group Hypermedia
 */
class HtmxOperationsTest extends UnitTestCase {

  /**
   * Htmx object to support operation testing.
   */
  private Htmx $htmx;

  /**
   * Tests for HtmxRequestOperationInterface operations need a URL.
   */
  private Url|MockObject $url;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->htmx = new Htmx();
    $this->url = $this->getMockBuilder('\Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->onlyMethods(['toString'])
      ->getMock();
    $this->url->expects($this->any())
      ->method('toString')
      ->willReturn('/example/path');
  }

  /**
   * Helper method to process the operations as if the render callback was used.
   *
   * @return array
   *   A stub of a processed render array.
   */
  protected function processOperations(): array {
    return RenderElementBase::processHtmxElement(['#htmx' => $this->htmx]);
  }

  /**
   * @covers \Drupal\Core\Render\Hypermedia\Operations\Insert
   */
  public function testInsert(): void {
    $insert = new Insert(
      select: '#source-value',
      target: '#target-value',
      url: $this->url,
    );
    $this->htmx->setRequestOperation($insert);
    $result = $this->processOperations();
    $headers = $result['#attached']['http_header'] ?? [];
    $attributes = $result['#attributes'] ?? [];
    $this->assertEquals([], $headers);
    $expected = [
      'data-hx-get' => '/example/path',
      'data-hx-select' => '#source-value',
      'data-hx-target' => '#target-value',
      'data-hx-swap' => 'beforeend  ignoreTitle:true',
    ];
    $this->assertEquals($expected, $attributes);
  }

  /**
   * @covers \Drupal\Core\Render\Hypermedia\Operations\Replace
   */
  public function testReplace(): void {
    $insert = new Replace(
      select: '#source-value',
      target: '#target-value',
      url: $this->url,
    );
    $this->htmx->setRequestOperation($insert);
    $result = $this->processOperations();
    $headers = $result['#attached']['http_header'] ?? [];
    $attributes = $result['#attributes'] ?? [];
    $this->assertEquals([], $headers);
    $expected = [
      'data-hx-get' => '/example/path',
      'data-hx-select' => '#source-value',
      'data-hx-target' => '#target-value',
      'data-hx-swap' => 'outerHTML  ignoreTitle:true',
    ];
    $this->assertEquals($expected, $attributes);
  }

}
