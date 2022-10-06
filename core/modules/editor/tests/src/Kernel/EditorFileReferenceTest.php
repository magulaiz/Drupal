<?php

declare(strict_types=1);

namespace Drupal\Tests\editor\Kernel;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\editor\Plugin\Filter\EditorFileReference;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests editor file reference filter plugin.
 *
 * @coversDefaultClass \Drupal\editor\Plugin\Filter\EditorFileReference
 * @group editor
 */
class EditorFileReferenceTest extends KernelTestBase {

  /**
   * Tests EditorFileReference image factory deprecation.
   *
   * @group legacy
   */
  public function testImageFactoryDeprecation(): void {
    $configuration = [];
    $plugin_id = 'editor_file_reference';
    $plugin_definition = ['provider' => 'editor'];
    $entity_repository = $this->prophesize(EntityRepositoryInterface::class);
    $image_factory = $this->prophesize(ImageFactory::class);
    $this->expectDeprecation('The property imageFactory (image.factory service) is deprecated in Drupal\editor\Plugin\Filter\EditorFileReference and will be removed before Drupal 11.0.0.');
    $editor_file_reference = new EditorFileReference($configuration, $plugin_id, $plugin_definition, $entity_repository->reveal(), $image_factory->reveal());
    $this->assertInstanceOf(ImageFactory::class, $editor_file_reference->imageFactory);
  }

}
