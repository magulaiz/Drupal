<?php

namespace Drupal\Tests\media\Functional\Update;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests media_update_9301() upgrade path.
 *
 * @group media
 * @see media_update_9301()
 */
class MediaEmbedViewModeUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.0.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests media_update_9301() upgrade path.
   */
  public function testMediaEmbedViewModeUpdate() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface */
    $module_installer = $this->container
      ->get('module_installer');
    $module_installer
      ->install(['media']);

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

    media_update_9301();

    $filter_format = FilterFormat::load('test_format');
    $settings = $filter_format->get('filters')['media_embed']['settings'];
    $this->assertEquals($settings['default_view_mode_9301'], 'view_mode_1');
  }

}
