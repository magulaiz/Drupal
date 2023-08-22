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
   * Tests that the media source plugin's existence is validated.
   */
  public function testMediaSourceIsValidated(): void {
    // We need to clear the current source configuration, or we will get
    // validation errors because the old configuration is not supported by the
    // new source.
    $this->entity->set('source_configuration', [])
      ->set('source', 'invalid');
    $this->assertValidationErrors([
      'source' => "The 'invalid' plugin does not exist.",
    ]);
  }

}
