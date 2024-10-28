<?php

declare(strict_types=1);

namespace Drupal\Tests\media\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests update functions for the Media module.
 *
 * @group media
 */
class MediaEmbedUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.bare.standard.php.gz',
    ];
  }

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'demo_umami';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['media', 'filter', 'contextual'];

  /**
   * Tests the update to add the show_contextual_links setting to media_embed filter.
   *
   * @see media_post_update_add_show_contextual_links_as_false()
   */
  public function testAddShowContextualLinksSetting(): void {
    // Create a filter format with the media_embed filter enabled, but without the new setting.
    $format = FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test Format',
      'filters' => [
        'media_embed' => [
          'status' => TRUE,
          'settings' => [],
        ],
      ],
    ]);
    $format->save();

    // Assert that the new setting is not present initially.
    $this->assertArrayNotHasKey('show_contextual_links', $format->get('filters')['media_embed']['settings']);

    // Run the update function.
    $this->runUpdates();

    // Reload the filter format configuration after running updates.
    $updated_format = FilterFormat::load('test_format');
    $media_embed_settings = $updated_format->get('filters')['media_embed']['settings'];

    // Assert that the new setting is added and set to FALSE.
    $this->assertArrayHasKey('show_contextual_links', $media_embed_settings, 'The show_contextual_links setting has been added.');
    $this->assertFalse($media_embed_settings['show_contextual_links'], 'The show_contextual_links setting is set to FALSE by default.');
  }

}
