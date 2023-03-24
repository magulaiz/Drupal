<?php

namespace Drupal\Tests\media\Functional\Update;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests media_update_10101() upgrade path.
 *
 * @group media
 * @see media_update_10101()
 */
class MediaEmbedViewModeUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Because the test manually installs media module, the entity type config
    // must be manually installed similar to kernel tests.
    $entity_type_manager = \Drupal::entityTypeManager();
    $media = $entity_type_manager->getDefinition('media');
    \Drupal::service('entity_type.listener')->onEntityTypeCreate($media);
    $media_type = $entity_type_manager->getDefinition('media_type');
    \Drupal::service('entity_type.listener')->onEntityTypeCreate($media_type);
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/media.php',
      __DIR__ . '/../../../fixtures/update/media-image.php',
    ];
  }

  /**
   * Tests media_update_10101() upgrade path.
   */
  public function testMediaEmbedViewModeUpdate() {
    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test format',
      'filters' => [
        'filter_align' => ['status' => TRUE],
        'filter_caption' => ['status' => TRUE],
        'media_embed' => ['status' => TRUE],
      ],
    ])->save();

    EntityViewMode::create([
      'id' => 'media.view_mode_1',
      'targetEntityType' => 'media',
      'status' => TRUE,
      'enabled' => TRUE,
      'label' => 'View Mode 1',
    ])->save();
    EntityViewMode::create([
      'id' => 'media.view_mode_2',
      'targetEntityType' => 'media',
      'status' => TRUE,
      'enabled' => TRUE,
      'label' => 'View Mode 2',
    ])->save();

    EntityViewDisplay::create([
      'id' => 'media.image.view_mode_1',
      'targetEntityType' => 'media',
      'status' => TRUE,
      'bundle' => 'image',
      'mode' => 'view_mode_1',
    ])->save();
    EntityViewDisplay::create([
      'id' => 'media.image.view_mode_2',
      'targetEntityType' => 'media',
      'status' => TRUE,
      'bundle' => 'image',
      'mode' => 'view_mode_2',
    ])->save();

    $filter_format = FilterFormat::load('test_format');
    $filter_format->setFilterConfig('media_embed', [
      'status' => TRUE,
      'settings' => [
        'default_view_mode' => 'view_mode_1',
        'allowed_media_types' => [],
        'allowed_view_modes' => [
          'view_mode_1' => 'view_mode_1',
          'view_mode_2' => 'view_mode_2',
        ],
      ],
    ])->save();

    $this->runUpdates();

    $filter_format = FilterFormat::load('test_format');
    $settings = $filter_format->get('filters')['media_embed']['settings'];
    $this->assertEquals($settings['default_view_mode_10101'], 'view_mode_1');
  }

}
