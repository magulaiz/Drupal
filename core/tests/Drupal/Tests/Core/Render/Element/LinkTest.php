<?php

namespace Drupal\Tests\Core\Render\Element;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Render\Element\Link;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGenerator;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Render\Element\Link
 * @group Render
 */
class LinkTest extends UnitTestCase {

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGenerator
   */
  protected $linkGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->linkGenerator = $this->prophesize(LinkGenerator::class);
    $container = new ContainerBuilder();
    $container->set('link_generator', $this->linkGenerator->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::preRenderLink
   *
   * @dataProvider providerTestPrerenderLink
   */
  public function testPrerenderLink($urlOptions, $elementOptions, $expected) {
    $url = new Url('<none>');
    $element = [
      '#url' => $url,
      '#title' => 'Test',
      '#options' => $elementOptions,
    ];
    $this->linkGenerator->generate($element['#title'], $url->setOptions($urlOptions))->willReturn(new GeneratedLink());
    Link::preRenderLink($element);
    $this->assertSame($expected, $element['#url']->getOptions());
  }

  /**
   * Data provider for testPrerenderLink().
   */
  public function providerTestPrerenderLink() {
    $data = [];
    $data['string keys'] = [
      [
      'fragment' => 'test',
      'query' => [
        'foo' => 'bar',
      ],
      ],
      [
        'query' => [
          'a' => 'b',
        ],
      ],
      [
        'fragment' => 'test',
        'query' => [
          'foo' => 'bar',
          'a' => 'b',
        ],
      ],
    ];

    $data['strings and unique integer keys'] = [
      [
        'query' => [
          'foo' => [
            100 => 100,
            101 => 101,
          ],
        ],
      ],
      [
        'query' => [
          'bar' => [
            200 => 200,
            'a' => 'b',
          ],
        ],
        'attributes' => [
          'class' => [
            'test',
          ],
        ],
      ],
      [
        'query' => [
          'foo' => [
            100 => 100,
            101 => 101,
          ],
          'bar' => [
            200 => 200,
            'a' => 'b',
          ],
        ],
        'attributes' => [
          'class' => [
            'test',
          ],
        ],
      ],
    ];

    $data['strings and overlapping integer keys'] = [
      [
        'attributes' => [
          'class' => [
            'foo',
          ],
        ],
      ],
      [
        'attributes' => [
          'class' => [
            'bar',
          ],
        ],
      ],
      [
        'attributes' => [
          'class' => [
            'foo',
            'bar',
          ],
        ],
      ],
    ];
    return $data;
  }

}
