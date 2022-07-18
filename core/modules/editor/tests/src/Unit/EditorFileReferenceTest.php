<?php

declare(strict_types=1);

namespace Drupal\Tests\editor\Unit;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\editor\Plugin\Filter\EditorFileReference;
use Drupal\Tests\UnitTestCase;

/**
 * Tests editor file reference filter plugin.
 *
 * @coversDefaultClass \Drupal\editor\Plugin\Filter\EditorFileReference
 * @group editor
 */
class EditorFileReferenceTest extends UnitTestCase {

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
    $this->expectDeprecation('Calling Drupal\editor\Plugin\Filter\EditorFileReference::__construct() with the $image_factory argument is deprecated in drupal:10.1.0 and is removed in drupal:11.0.0. See https://www.drupal.org/node/3173719');
    new EditorFileReference($configuration, $plugin_id, $plugin_definition, $entity_repository->reveal(), $image_factory->reveal());
  }

}
