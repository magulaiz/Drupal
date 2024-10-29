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
      __DIR__ . '/../../../../../media/tests/fixtures/update/drupal-11.0.5.filled.demo_umami.php.gz',
    ];
  }

  /**
   * Tests the update to add the show_contextual_links setting to media_embed filter.
   *
   * @see media_post_update_add_show_contextual_links_as_false()
   */
  public function testAddShowContextualLinksSetting(): void {

    // Create a filter format with the media_embed filter enabled, but without the new setting.
    $new_format = FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test Format',
      'filters' => [
        'media_embed' => [
          'status' => TRUE,
          'settings' => [],
        ],
      ],
    ]);
    $new_format->save();
    // Assert that the new setting is present but unset initially.
    $this->assertArrayHasKey('show_contextual_links', $new_format->get('filters')['media_embed']['settings']);
    // Get the umami format.
    $existing_full_html = FilterFormat::load('full_html');
    // Assert that the new setting is not present.
    $this->assertArrayNotHasKey('show_contextual_links', $existing_full_html->get('filters')['media_embed']['settings']);
    $existing_basic_html = FilterFormat::load('basic_html');
    // Assert that the new setting is not present.
    $this->assertArrayNotHasKey('show_contextual_links', $existing_basic_html->get('filters')['media_embed']['settings']);

    // Run the update function.
    $this->runUpdates();
    // Reload the filter format configuration after running updates.
    $updated_format = FilterFormat::load('test_format');
    $updated_media_embed_settings = $updated_format->get('filters')['media_embed']['settings'];
    $updated_full_html = FilterFormat::load('full_html');
    $updated_basic_html = FilterFormat::load('basic_html');
    // Assert that the new setting is added and set to FALSE.
    $this->assertArrayHasKey('show_contextual_links', $updated_format->get('filters')['media_embed']['settings'], 'The show_contextual_links setting has been added.');
    $this->assertFalse($updated_format->get('filters')['media_embed']['settings']['show_contextual_links'], 'The show_contextual_links setting is set to FALSE by default.');
    // Assert that the new setting is added to the full_html format and set to FALSE after running the update.
    $this->assertArrayHasKey('show_contextual_links', $updated_full_html->get('filters')['media_embed']['settings'], 'The show_contextual_links setting has been added.');
    $this->assertFalse($updated_full_html->get('filters')['media_embed']['settings']['show_contextual_links'], 'The show_contextual_links setting is set to FALSE by default.');
    // Assert that the new setting is added to the basic_html format and set to FALSE after running the update.
    $this->assertArrayHasKey('show_contextual_links', $updated_basic_html->get('filters')['media_embed']['settings'], 'The show_contextual_links setting has been added.');
    $this->assertFalse($updated_basic_html->get('filters')['media_embed']['settings']['show_contextual_links'], 'The show_contextual_links setting is set to FALSE by default.');
    // Assert that the new setting is added and set to FALSE.
    $this->assertArrayHasKey('show_contextual_links', $updated_media_embed_settings, 'The show_contextual_links setting has been added.');
    $this->assertFalse($updated_media_embed_settings['show_contextual_links'], 'The show_contextual_links setting is set to FALSE by default.');
  }

}
