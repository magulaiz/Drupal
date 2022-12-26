<?php

namespace Drupal\KernelTests\Core\File;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests deprecated file features.
 *
 * @group legacy
 * @group File
 */
class MimeTypeLegacyTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'file_deprecated_test'];

  /**
   * Tests deprecation of hook_file_mimetype_mapping_alter.
   *
   * @expectedDeprecation The deprecated alter hook hook_file_mimetype_mapping_alter() is implemented in these functions: file_deprecated_test_file_mimetype_mapping_alter. This hook is deprecated in Drupal 8.7.x and will be removed before Drupal 9.0.0. Implement hook_mimetype_alter() instead. See https://www.drupal.org/node/2311679.
   */
  public function testHookFileMimetypeMappingAlter() {
    $mapper = $this->container->get('file.mime_type.mapper');
    $this->assertEquals(['file_test_2', 'file_test_3'], $mapper->getExtensionsForMimeType('madeup/file_test_2'));
  }

}
