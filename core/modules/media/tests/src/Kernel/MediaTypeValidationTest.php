<?php

namespace Drupal\Tests\media\Kernel;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests validation of media_type entities.
 *
 * @group media
 */
class MediaTypeValidationTest extends ConfigEntityValidationTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field', 'media', 'media_test_source'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->entity = $this->createMediaType('test', ['id' => 'test_media']);
  }

  /**
   * Tests that the `source` property of a media type cannot be changed.
   */
  public function testSourceIsImmutable(): void {
    // Clear the previous source configuration, to avoid a validation error
    // arising from the fact that the `image` source has different configuration
    // keys.
    $this->entity->set('source_configuration', []);

    $this->entity->set('source', 'image');
    $this->assertValidationErrors([
      '' => "The 'source' property cannot be changed.",
    ]);
  }

}
