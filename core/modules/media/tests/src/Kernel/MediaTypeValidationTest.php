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
   * {@inheritdoc}
   */
  public function testImmutableProperties(array $valid_values = [], array $indirect_consequences = []): void {
    parent::testImmutableProperties($valid_values, [
      'source' => [
        'source_configuration' => [
          "'source_field' is an extraneous key because source is <RANDOM> (see config schema type media.source.*).",
          "'test_config_value' is an extraneous key because source is <RANDOM> (see config schema type media.source.*).",
        ],
      ],
    ]);
  }

}
