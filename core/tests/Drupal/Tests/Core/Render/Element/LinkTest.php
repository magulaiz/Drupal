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
   */
  public function testPrerenderLink() {
    $options = [
      'query' => [
        'foo' => [
          100 => 100,
        ],
        'bar' => [
          200 => 200,
        ],
      ],
    ];
    $url = new Url('<none>', [], $options);
    $element = [
      '#url' => $url,
      '#title' => 'Test',
      '#options' => $options,
    ];
    $this->linkGenerator->generate($element['#title'], $url->setOptions($options))->willReturn(new GeneratedLink());
    Link::preRenderLink($element);
    $this->assertSame($options, $element['#url']->getOptions());
  }

}
