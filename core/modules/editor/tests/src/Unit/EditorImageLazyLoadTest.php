<?php

declare(strict_types = 1);

namespace Drupal\Tests\editor\Unit;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Image\Image;
use Drupal\Core\Image\ImageFactory;
use Drupal\file\FileInterface;
use Drupal\editor\Plugin\Filter\EditorImageLazyLoad;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\editor\Plugin\Filter\EditorImageLazyLoad
 * @group filter
 */
final class EditorImageLazyLoadTest extends UnitTestCase {

  /**
   * @var \Drupal\editor\Plugin\Filter\EditorImageLazyLoad
   */
  protected EditorImageLazyLoad $filter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $file = $this->prophesize(FileInterface::class);
    $file->getFileUri()->willReturn('foo.png');
    $entity_repository = $this->prophesize(EntityRepositoryInterface::class);
    $entity_repository->loadEntityByUuid('file', 'a6d88b01-3b5e-4c02-bf26-24a0c48d61cd')->willReturn($file->reveal());
    $image = $this->prophesize(Image::class);
    $image->getHeight()->willReturn(100);
    $image->getWidth()->willReturn(100);
    $image_factory = $this->prophesize(ImageFactory::class);
    $image_factory->get('foo.png')->willReturn($image->reveal());
    $this->filter = new EditorImageLazyLoad([], 'editor_image_lazy_load', ['provider' => 'test'], $entity_repository->reveal(), $image_factory->reveal());
    parent::setUp();
  }

  /**
   * @covers ::process
   *
   * @dataProvider providerHtml
   *
   * @param string $html
   *   Input HTML.
   * @param string $expected
   *   The expected output string.
   */
  public function testProcess(string $html, string $expected): void {
    $this->assertSame($expected, $this->filter->process($html, 'en')->getProcessedText());
  }

  /**
   * Provides data for testProcess.
   *
   * @return array
   *   An array of test data.
   */
  public function providerHtml(): array {
    return [
      'lazy loading attribute already added' => ['<p><img src="foo.png" loading="lazy"></p>', '<p><img src="foo.png" loading="lazy" /></p>'],
      'eager loading attribute already added' => ['<p><img src="foo.png" loading="eager"/></p>', '<p><img src="foo.png" loading="eager" /></p>'],
      'image dimensions already provided' => ['<p><img src="foo.png" width="200" height="200"/></p>', '<p><img src="foo.png" width="200" height="200" loading="lazy" /></p>'],
      'no image tag' => ['<p>Lorem ipsum...</p>', '<p>Lorem ipsum...</p>'],
      'no loading attribute nor uuid' => ['<p><img src="foo.png"></p>', '<p><img src="foo.png" /></p>'],
      'no loading attribute with uuid' => ['<p><img src="foo.png" data-entity-type="file" data-entity-uuid="a6d88b01-3b5e-4c02-bf26-24a0c48d61cd"></p>', '<p><img src="foo.png" data-entity-type="file" data-entity-uuid="a6d88b01-3b5e-4c02-bf26-24a0c48d61cd" width="100" height="100" loading="lazy" /></p>'],
      'eager loading attribute with uuid' => ['<p><img src="foo.png" data-entity-type="file" data-entity-uuid="a6d88b01-3b5e-4c02-bf26-24a0c48d61cd" loading="eager"></p>', '<p><img src="foo.png" data-entity-type="file" data-entity-uuid="a6d88b01-3b5e-4c02-bf26-24a0c48d61cd" loading="eager" width="100" height="100" /></p>'],
    ];
  }

}
