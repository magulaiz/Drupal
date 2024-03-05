<?php

declare(strict_types=1);

namespace Drupal\Tests\image\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests addition of new image resize_policy setting.
 *
 * @group Update
 */
class ResizePolicySettingUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests image_post_update_add_resize_policy().
   */
  public function testSystemPostUpdateLinksetSettings() {
    $storage = \Drupal::entityTypeManager()->getStorage('field_config');
    $imageFields = $storage->loadMultiple();
    if (!empty($imageFields)) {
      foreach ($imageFields as $field) {
        if ($field->getType() === 'image') {
          $this->assertNull($field->getSetting('resize_policy'));
        }
      }
    }

    $this->runUpdates();

    // Confirm that config settings was added and is FALSE by default.
    $storage = \Drupal::entityTypeManager()->getStorage('field_config');
    $imageFields = $storage->loadMultiple();
    if (!empty($imageFields)) {
      foreach ($imageFields as $field) {
        if ($field->getType() === 'image') {
          $this->assertEquals('resize_larger_images', $field->getSetting('resize_policy'));
        }
      }
    }
  }

}
